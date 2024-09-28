import requests
from bs4 import BeautifulSoup
import logging
import utils
import database

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"


# function to extract the recipe url from a overview site
def scrap_recipe_overview(url, website, existing_recipes): 
    new_recipes_count = 0
    
    header = {
        'User-Agent': "FindMyRecipeBot/1.0 (+https://finde-mein-rezept.de/botinfo)"
    }
    response = requests.get(url, headers=header)

    if response.status_code != 200:
        logging.error(f"{RED} Error when calling up the overview page. [{response.status_code}]")
        return []
    
    soup = BeautifulSoup(response.text, 'html.parser')

    links = [a['href'] for a in soup.select(website['selectors']['recipe']) if a.has_attr('href')]
    if not links:
        links = [div["data-url"] for div in soup.select(website['selectors']['recipe']) if div.has_attr("data-url")]
        links = [link.replace("|", "/") for link in links]

    for link in links:
        try:
            if "artikel" in link.lower() or "article" in link.lower():
                continue
            if link in existing_recipes:
                continue

            response = requests.get(link, headers=header)

            if response.status_code != 200:
                print(f"{RED} Error when calling up the recipe page. {RESET}{link} {RED}[{response.status_code}]{RESET}")
                continue

            soup = BeautifulSoup(response.content, 'html.parser')
            title, description, image_url, ingredients = parse_recipe(soup, website)
            print(f"{GREEN}A new recipe was found {RESET}   {title}")
            
            recipe_id = database.insert_recipe(title, description, website['source_id'], link, image_url)
            for ingredient in ingredients:
                ingredient_id = database.get_or_create_ingredient(ingredient)
                database.insert_recipe_ingredient(recipe_id, ingredient_id)
            new_recipes_count += 1
        except Exception as e:
            print(f"Error scraping {website['name']}: {e}")
    return new_recipes_count



def parse_recipe(soup, config):
    type_mapping = {
        'html': parse_html,
        'table': parse_table,
        'plainTableGK': parse_plain_table_gk,
        'list': parse_list,
        'meta': parse_meta
    }

    parser_function = type_mapping.get(config['type'])
    
    if parser_function:
        return parser_function(soup, config['selectors'])
    else:
        raise ValueError(f"Unsupported type: {config['type']}")
    
def parse_html(soup, selectors):
    title_tag = soup.select_one(selectors['title'])
    title = title_tag.get_text().strip() if title_tag else "Kein Titel gefunden"   

    ingredients = []
    ingredients_list = [row.get_text(strip=True) for row in soup.select(selectors['ingredients'])]
    for ingredient in ingredients_list:
        split_ingredient = ingredient.split(" ", 1)
        if len(split_ingredient) > 0:
            if len(split_ingredient) == 2:
                ingredient_name = utils.clean_ingredient_name(split_ingredient[1])
            else:
                ingredient_name = utils.clean_ingredient_name(split_ingredient[0])

            ingredients.append(ingredient_name)

    description_tag = soup.select_one(selectors['description'])
    description = description_tag.get_text().strip() if description_tag else ""
    
    image_tag = soup.select_one(selectors['image'])
    image_url = image_tag['src'] if image_tag and 'src' in image_tag.attrs else ""
    return title, description, image_url, ingredients

def parse_table(soup, selectors):
    title_tag = soup.select_one(selectors['title'])
    title = title_tag.get_text().strip() if title_tag else "Kein Titel gefunden"    

    ingredients = [row.get_text(strip=True) for row in soup.select(selectors['ingredients'])]

    description_tag = soup.select_one(selectors['description'])
    description = description_tag.get_text().strip() if description_tag else ""
    
    image_tag = soup.select_one(selectors['image'])
    image_url = image_tag['src'] if image_tag and 'src' in image_tag.attrs else ""
    return title, ingredients, description, image_url

def parse_list(soup, selectors):
    title_tag = soup.select_one(selectors['title'])
    title = title_tag.get_text().strip() if title_tag else "Kein Titel gefunden"    
    ingredients = [li.get_text(strip=True) for li in soup.select(selectors['ingredients'])]

    description_tag = soup.select_one(selectors['description'])
    description = description_tag.get_text().strip() if description_tag else ""

    image_url = soup.select_one(selectors['image'])['src']
    return title, ingredients, description, image_url

def parse_meta(soup, selectors):
    title_tag = soup.select_one(selectors['title'])['content']
    title = title_tag.get_text().strip() if title_tag else "Kein Titel gefunden"    
    ingredients = soup.select_one(selectors['ingredients'])['content'].split(', ')

    description_tag = soup.select_one(selectors['description'])['content']
    description = description_tag.get_text().strip() if description_tag else ""

    image_url = soup.select_one(selectors['image'])['content']
    return title, ingredients, description, image_url

def parse_plain_table_gk(soup, selectors):
    title_tag = soup.select_one(selectors['title'])
    title = title_tag.get_text().strip() if title_tag else "Kein Titel gefunden"    

    description = soup.select_one(selectors['description'])['content']

    if 'descriptionMeta' in selectors:
        description = soup.select_one(selectors['description'])['content']
    else:
        description = soup.select_one(selectors['description']).get_text(strip=True)
    
    if 'imageMeta' in selectors:
        image_url = soup.select_one(selectors['image'])['content']
    else:
        image_url = soup.select_one(selectors['image'])['src']

    ingredients = []
    ingredients_table = soup.find_all('table')
    if ingredients_table:
        for table in ingredients_table:
            rows = table.find_all('tr')
            for row in rows:
                columns = row.find_all('th')
                if len(columns) == 2:
                   ingredients.append(columns[1].get_text(strip=True))


    print(ingredients)
    return title, description, image_url, ingredients
