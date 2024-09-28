import requests
from bs4 import BeautifulSoup
import mysql.connector
from mysql.connector import Error
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import re
import json
import time
import utils
import sys

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

# Funktion, um die MySQL-Verbindung herzustellen
def create_connection():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='rezept',
            user='root',
            password='',
            charset='utf8mb4',  # WICHTIG: Zeichensatz für die Verbindung setzen
            collation='utf8mb4_unicode_ci'
        )
        if connection.is_connected():
            print(f"{GREEN}MySQL Database connection successfully")
        return connection
    except Error as e:
        print(f"{RED}MySQL Database connection error: {RESET}{e}")
        return None

# Funktion, um die Konfigurationen aus einer JSON-Datei zu laden
def load_website_configs(filename='urls.json'):
    with open(filename, 'r') as file:
        return json.load(file)

# Funktion, um alle Rezept-URLs aus der Datenbank zu holen
def get_existing_recipes(connection):
    cursor = connection.cursor()
    cursor.execute("SELECT url FROM recipes")
    results = cursor.fetchall()
    return set([row[0] for row in results])  # Rückgabe eines Sets von URLs (schnellere Abfragen)

# Funktion, um ein Rezept in die Datenbank einzufügen
def insert_recipe(connection, title, description, source_id, url, image_url, duration):
    cursor = connection.cursor()
    query = "INSERT INTO recipes (title, description, source_id, url, image_url, duration) VALUES (%s, %s, %s, %s, %s, %s)"
    cursor.execute(query, (title, description, source_id, url, image_url, duration))
    connection.commit()
    return cursor.lastrowid  # Rückgabe der ID des eingefügten Rezepts

# Funktion, um eine Zutat in die Datenbank einzufügen oder ihre ID zu holen
def get_or_create_ingredient(connection, ingredient_name):
    cursor = connection.cursor()
    query = "SELECT id FROM ingredients WHERE name = %s"
    cursor.execute(query, (ingredient_name,))
    result = cursor.fetchone()
    
    if result:
        return result[0]  # Rückgabe der existierenden Zutat-ID
    else:
        query = "INSERT INTO ingredients (name) VALUES (%s)"
        cursor.execute(query, (ingredient_name,))
        print("Hinzugefügt")
        connection.commit()
        return cursor.lastrowid  # Rückgabe der neuen Zutat-ID

# Funktion, um eine Zutat einem Rezept hinzuzufügen (mit Menge)
def insert_recipe_ingredient(connection, recipe_id, ingredient_id):
    cursor = connection.cursor()
    query_check = "SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = %s AND ingredient_id = %s"
    cursor.execute(query_check, (recipe_id, ingredient_id))
    exists = cursor.fetchone()[0] > 0

    if not exists:
        query_insert = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (%s, %s)"
        cursor.execute(query_insert, (recipe_id, ingredient_id))
        connection.commit()
        

# Funktion, um Mengenangaben aus dem Zutatennamen zu bereinigen
def clean_ingredient_name(ingredient_text):
    cleaned_text = re.sub(r'^\s*(ml|g|d|cl|kg|l|Stk\.|EL|TL)\s+', '', ingredient_text)
    cleaned_text = cleaned_text.replace('\n', ' ').strip()
    return cleaned_text.strip()

# Funktion zum Extrahieren von Rezepten von einer einzelnen Seite
def scrape_recipe(url, connection, source_id, existing_recipes, title_selector, ingredient_selector, description_selector, image_selector, duration_selector, new_recipes_count):
    if url in existing_recipes:
        return new_recipes_count

    header = {
        'User-Agent': 'FindMyRecipeBot/1.0 (+https://finde-mein-rezept.de/botinfo)' 
    }
    response = requests.get(url, headers=header)
    if response.status_code != 200:
        print(f"{RED}Fehler beim Abrufen der Seite: {RESET}{url}")
        return new_recipes_count
    
    soup = BeautifulSoup(response.content, 'html.parser', from_encoding='utf-8')


    if source_id == 4: # Waskochich.com
        # Anpassung: Beschreibung aus dem <meta> Tag mit itemprop="description" extrahieren
        description_tag = soup.find('meta', {'itemprop': 'description'})
        description = description_tag['content'].strip() if description_tag and 'content' in description_tag.attrs else ""

        image_tag = soup.find('meta', {'itemprop': 'image'})
        image_url = image_tag.get_text().strip() if image_tag and 'content' else ""

        time = utils.extract_time_from_html(soup, 'prepTime')
        time2 = utils.extract_time_from_html(soup, 'cookTime')
        duration = utils.add_durations(time, time2)
    else:
        description_tag = soup.select_one(description_selector)
        description = description_tag.get_text().strip() if description_tag else ""

         # Bild-URL extrahieren
        image_tag = soup.select_one(image_selector)
        image_url = image_tag['src'] if image_tag and 'src' in image_tag.attrs else ""
        
        # Dauer des Rezeptes extrahieren
        duration_tag = soup.select_one(duration_selector)
        duration = duration_tag.get_text().strip() if duration_tag else "n/a"

    title_tag = soup.select_one(title_selector)
    title = title_tag.get_text().strip() if title_tag else "Kein Titel gefunden"    

    recipe_id = insert_recipe(connection, title, description, source_id, url, image_url, duration)
    print(f"{GREEN}New recipe found: {RESET}{title}: {url}")

    # GuteKueche.de Table Zutaten
    if source_id == 3: # GuteKueche.de
            # Finden aller Tabellen, die Zutaten enthalten
            ingredients_tables = soup.find_all('table')

            # Überprüfen, ob Tabellen gefunden wurden
            if ingredients_tables:
                print("Zutaten:")
                # Jede Tabelle durchlaufen
                for table in ingredients_tables:
                    # Alle Zeilen in der Tabelle durchlaufen
                    rows = table.find_all('tr')
                    for row in rows:
                        columns = row.find_all('th')  # Hier verwenden wir 'th', da die Zutaten in 'th' stehen
                        if len(columns) == 2:  # Es gibt zwei <th>-Zellen pro Zeile
                            amount = columns[0].get_text(strip=True)  # Menge der Zutat
                            ingredient_name = columns[1].get_text(strip=True)  # Name der Zutat
                            print(f"{ingredient_name}")
            else:
                print("Keine Zutaten-Tabellen gefunden.")


            ingredient_name = clean_ingredient_name(ingredient_name)
            ingredient_id = get_or_create_ingredient(connection, ingredient_name)
            
            insert_recipe_ingredient(connection, recipe_id, ingredient_id)

    else:
    
        ingredients_list = soup.select(ingredient_selector)

        for ingredient_tag in ingredients_list:

            if source_id == 4:
                # Extrahiere die Menge und den Namen der Zutat
                amount = ingredient_tag.find('td', class_='text-right')
                name = ingredient_tag.find_all('th')[1]  # Der Name ist der zweite <th>

                if amount and name:
                    ingredient_name = name.get_text(strip=True)
                    print(ingredient_name)
                    ingredient_id = get_or_create_ingredient(connection, ingredient_name)
                    insert_recipe_ingredient(connection, recipe_id, ingredient_id)
            
            else:
                ingredient_text = ingredient_tag.get_text().strip()
                split_ingredient = ingredient_text.split(" ", 1)

                if len(split_ingredient) == 2:
                    ingredient_name = split_ingredient
                else:
                    ingredient_name = split_ingredient[0]

                ingredient_name = clean_ingredient_name(ingredient_name)
                ingredient_id = get_or_create_ingredient(connection, ingredient_name)
                insert_recipe_ingredient(connection, recipe_id, ingredient_id)

    new_recipes_count += 1
    return new_recipes_count

# Funktion zum Crawlen der Rezept-Links von einer Übersichtsseite
def get_recipe_links(start_url, recipe_selector, source_id):
    header = {
        'User-Agent': 'FindMyRecipeBot/1.0 (+https://finde-mein-rezept.de/botinfo)' 
    }
    response = requests.get(start_url, headers=header)
    if response.status_code != 200:
        print(f"{RED}Fehler beim Abrufen der Startseite: {RESET}{start_url}")
        return []

    soup = BeautifulSoup(response.text, 'html.parser')

    if source_id == 2:
        links = [div["data-url"] for div in soup.select(recipe_selector) if div.has_attr("data-url")]
        links = [link.replace("|", "/") for link in links]
    else:
        links = [a['href'] for a in soup.select(recipe_selector) if a['href']]
    return links

# Funktion zum Crawlen der Rezept-Links von einer dynamischen Seite
def get_recipe_links_dynamic(start_url, recipe_selector, source_id):
    options = webdriver.ChromeOptions()
    options.add_argument('--headless')  # Optional: Fensterloser Modus
    driver = webdriver.Chrome(options=options)

    try:
        driver.get(start_url)
        time.sleep(3)  # Warte, bis die Seite geladen ist

        links = set()
        last_height = driver.execute_script("return document.body.scrollHeight")

        while True:
            # Sammle alle Rezept-Links auf der aktuellen Seite
            recipe_elements = driver.find_elements(By.CSS_SELECTOR, recipe_selector)
            for element in recipe_elements:
                link = element.get_attribute('href')
                if link:
                    links.add(link)

            # Scrolle nach unten, um mehr Inhalte zu laden
            driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
            time.sleep(3)  # Warte auf das Nachladen der Inhalte

            # Überprüfe, ob das Ende der Seite erreicht ist
            new_height = driver.execute_script("return document.body.scrollHeight")
            if new_height == last_height:
                break  # Ende der Seite erreicht
            last_height = new_height

    finally:
        driver.quit()
    
    return list(links)

# Hauptfunktion zum Starten des Crawlings und Scrapings
def main():
    new_recipes_count = 0
    start_time = time.time()
    try:
        connection = create_connection()
        if not connection:
            return

        website_configs = load_website_configs()  # Laden der Website-Konfigurationen aus der JSON-Datei
        existing_recipes = get_existing_recipes(connection)
        print(f"{RESET}{len(existing_recipes)} {GREEN}bestehende Rezepte geladen.")

        for website in website_configs['websites']:
            print(f"{CYAN}Scraping {RESET}{website['name']}...")

            for page_num in range(1, website['num_pages'] + 1):
                page_url = f"{website['start_url']}{website['page_param']}{page_num}"
                print(f"{YELLOW}Scraping Page: {RESET}{page_url}")
                
                if website['source_id'] == 88:
                    recipe_links = get_recipe_links_dynamic(website['start_url'], website['selectors']['recipe_selector'], website['source_id'])
                else:
                    recipe_links = get_recipe_links(page_url, website['selectors']['recipe_selector'], website['source_id'])

                for link in recipe_links:
                    if "artikel" in link.lower() or "article" in link.lower():
                        continue

                    full_link = requests.compat.urljoin(website['start_url'], link)
                    new_recipes_count = scrape_recipe(full_link, connection, website['source_id'], existing_recipes, 
                                website['selectors']['title_selector'], 
                                website['selectors']['ingredient_selector'], 
                                website['selectors']['description_selector'],
                                website['selectors']['image_selector'],
                                website['selectors']['duration_selector'],
                                new_recipes_count)

        connection.close()
        print(f"{GREEN}Alle Seiten gescraped und Rezepte erfolgreich in die Datenbank eingefügt.")
    except KeyboardInterrupt:
        print(f"{RED}Ausführung wurde durch Benutzer beendet!{RESET}")
        

    finally:
        if connection.is_connected():
            connection.close()
            print(f"{RESET}Datenbankverbindung {RED}geschlossen.{RESET}")

        print(f"{new_recipes_count} {GREEN}neue {RESET}Rezepte gespeichert.")

        end_time = time.time()
        elapsed_time = end_time - start_time
        # Umwandlung der Sekunden in Stunden, Minuten und Sekunden
        hours, rem = divmod(elapsed_time, 3600)  # 3600 Sekunden = 1 Stunde
        minutes, seconds = divmod(rem, 60)       # 60 Sekunden = 1 Minute

        # Ausgabe in Stunden, Minuten und Sekunden, je nach Länge der Laufzeit
        if hours > 0:
            print(f"Laufzeit des Crawlers: {int(hours)} Stunden, {int(minutes)} Minuten & {int(seconds)} Sekunden")
        elif minutes > 0:
            print(f"Laufzeit des Crawlers: {int(minutes)} Minuten und {int(seconds)} Sekunden")
        else:
            print(f"Laufzeit des Crawlers: {int(seconds)} Sekunden")

if __name__ == '__main__':
    main()
