import scrapy
import yaml
import re
from bs4 import BeautifulSoup
from notifications import Notification
import logging
from scrapy.utils.log import configure_logging 
from recipecrawler.logger_config import setup_logger

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

class RecipecSpider(scrapy.Spider):
    name = "recipe"

    custom_settings = {
        'USER_AGENT': 'FindMyRecipeBot/1.0 (+https://finde-mein-rezept.de/botinfo)',  # Benutzerdefinierter User-Agent
    }
    logger = setup_logger(__name__)

    def __init__(self, *args, **kwargs):
        super(RecipecSpider, self).__init__(*args, **kwargs)
        self.notification = Notification()
        self.newRecipesCounter = 0
        self.skippedRecipesCounter = 0
        self.foundedRecipesCounter = 0
        self.crawl_websites = kwargs.get('website', '').split(',') if 'website' in kwargs else []

    @classmethod
    def from_crawler(cls, crawler, *args, **kwargs):
        return super(RecipecSpider, cls).from_crawler(crawler, *args, **kwargs)

    def start_requests(self):
        with open('websites.yaml', 'r') as stream:
            try:
                cfg = yaml.safe_load(stream)
            except yaml.YAMLError as exc:
                logging.log(logging.ERROR, f"Error in configuration file: {exc}")
                return
            
        for website in cfg['websites']:
            if self.crawl_websites and website['name'] not in self.crawl_websites:
                continue

            url = website['url']
            meta = {'selectors': website['selectors']}
            yield scrapy.Request(url=url, callback=self.parse_overview, meta=meta)

    def parse_overview(self, response):
        selectors = response.meta['selectors']
        
        for recipe_link in response.css(selectors['recipe_link']).getall():
            recipe_link = recipe_link.replace('|', '/')
            if "document.location.href" in recipe_link:
                recipe_link = recipe_link.replace("document.location.href='", "")
                recipe_link = recipe_link.strip().strip("'")
                recipe_link = response.urljoin(recipe_link)
                start = recipe_link.find("'/") + 1  
                end = recipe_link.find("'", start) 
                recipe_link = recipe_link[start:end]
            if not recipe_link.startswith(('http://', 'https://')):
                domain = response.url.split('//')[-1].split('/')[0]
                recipe_link = f"{response.url.split('//')[0]}//{domain}/{recipe_link.lstrip('/')}"
            
            #self.logger.debug(f"Recipe link: {recipe_link}")
            yield response.follow(recipe_link, callback=self.parse_recipe, meta={'selectors': selectors})

        if selectors['next_page']:
            next_page = response.css(selectors['next_page']).get()
            if next_page:
                next_page = next_page.replace('|', '/')

                if "document.location.href" in next_page:
                    next_page = next_page.replace("document.location.href='", "")
                    next_page = response.urljoin(next_page)
                    start = next_page.find("'/") + 1  
                    end = next_page.find("'", start) 
                    next_page = next_page[start:end]

                if not next_page.startswith(('http://', 'https://')):
                    domain = response.url.split('//')[-1].split('/')[0]
                    next_page = f"{response.url.split('//')[0]}//{domain}/{next_page.lstrip('/')}"
                yield response.follow(next_page, callback=self.parse_overview, meta={'selectors': selectors})

    def parse_recipe(self, response):
        selectors = response.meta['selectors']

        title = response.css(selectors['title']).get()
        img = response.css(selectors['image']).get()
        
        self.logger.debug(f"Title: {title}")
        self.logger.debug(f"Image: {img}")

        description_element = response.css(selectors['description']).get()
        description = BeautifulSoup(description_element, 'lxml').get_text(strip=True) if description_element else None
        self.logger.debug(f"Description: {description}")

        #ingredients
        soup = BeautifulSoup(response.text, 'lxml')
        ingredients_list = soup.select(selectors['ingredients'])

        ingredients = []
        for item in ingredients_list:
            # Entferne den <span> und <small> Inhalt
            for span in item.find_all('span'):
                span.decompose()
            for small in item.find_all('small'):
                small.decompose()
            # Füge den bereinigten Text zur Liste hinzu
            ingredient_name = item.get_text(strip=True)
            cleaned_ingredient = self.clean_ingredient(ingredient_name)
            if cleaned_ingredient:
                ingredients.append(cleaned_ingredient)

        for ingredient in ingredients:
            self.logger.debug(ingredient)
        #ingredients = [BeautifulSoup(ingredient, features="lxml").get_text(strip=True) for ingredient in ingredients]
        
        self.foundedRecipesCounter += 1
        if self.foundedRecipesCounter % 66 == 0:
            self.logger.info(f"{BLUE}Progress: Found recipes: {RESET}{self.foundedRecipesCounter} {GREEN}({self.newRecipesCounter}) {BLUE}Skipped recipes: {RESET}{self.skippedRecipesCounter}{RESET}")
        
        yield {
            'title': title ,
            'image': img,
            'description': description,
            'ingredients': ingredients,
            'url': response.url,
        }
    def clean_ingredient(self, ingredient):
        # Entferne Mengenangaben mit Maßeinheiten
        ingredient = re.sub(r'\b\d+(\.\d+)?\s*(kg|g|l|ml|EL|TL|Stück|Tasse|Tassen|Prise|Scheiben|Stück|Stücke|Kopf|gemahlen|getrocknet|kl.|Esslöffel|Teelöffel|Liter|Gramm|½|Stk.|Stück|)\b', '', ingredient, flags=re.IGNORECASE)
        # Entferne Mengenangaben ohne Maßeinheiten
        ingredient = re.sub(r'\b\d+(\.\d+)?\b', '', ingredient)
        # Entferne Klammern und deren Inhalt
        ingredient = re.sub(r'\(.*?\)', '', ingredient)
         # Entferne Strings mit nur einem Zeichen
        ingredient = re.sub(r'\b\w\b', '', ingredient)
        # Entferne überflüssige Leerzeichen, Kommas und Bindestriche
        ingredient = re.sub(r'[-,\s]+', ' ', ingredient).strip()
        return ingredient
    
    def clean_ingredient_name(self, ingredient_text):
        # Entfernt alle Zahlen aus dem String
        cleaned_text = re.sub(r'\d+', '', ingredient_text)
        cleaned_text = re.sub(r'\d+.*?(\s|$)', '', ingredient_text)  # Entfernt Zahlen und alles danach
        cleaned_text = re.sub(r'\d+\s*[gml]|(?::g|ml|Esslöffel|Prise|Kopf|etwas|nach Geschmack|nach Bedarf|etwas|½|Bund|g|EL|TL|Teelöffel|TL.|El.)', '', cleaned_text)  # Entfernt Mengenangaben
        cleaned_text = re.sub(r'\s*/.*$', '', cleaned_text)  # Entfernt alles nach dem Schrägstrich
        cleaned_text = re.sub(r'\(.*?\)', '', cleaned_text)  # Entfernt alles in Klammern
        cleaned_text = re.sub(r'\w+:\s*', '', cleaned_text) # Entfernt Wörter, die mit einem Doppelpunkt enden
        cleaned_text = cleaned_text.replace('\n', ' ').strip()
        return cleaned_text.strip()
        

