import time
import json
import database
import scraper
import logging
import argparse

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

def main(save_to_db):
    start_time = time.time()
    try:
        connection = database.create_connection()
        # Loading the website config urls.json
        with open("urls.json", 'r') as file:
            website_configs = json.load(file)
    
        existing_recipes = database.get_existing_recipes()
        print(f"{RESET}{len(existing_recipes)} {GREEN} recipes have been loaded from the database..")

        for website in website_configs['websites']:
            print(f"{CYAN}Scraping the following page {RESET}{website['url']}")

            if 'pages' in website:
                for page_num in range(1, website['pages'] + 1):
                    page_url = f"{website['url']}{website['page_param']}{page_num}"
                    print(f"{CYAN}Scraping page number {RESET}{page_num} {CYAN}of {RESET}{website['pages']}")
                    scraper.scrap_recipe_overview(page_url, website, existing_recipes, save_to_db)
                    # recipe_id = database.insert_recipe(connection, title, description, website['source_id'], page_url, image_url)
            else:
                scraper.scrap_recipe_overview(website["url"], website, existing_recipes, save_to_db)
                
        


    except KeyboardInterrupt:
        print(f"{RED}Execution was terminated by user..{RESET}")

    finally:
        if connection.is_connected():
            connection.close()
            print(f"{RED}MySQL database connection has closed!")


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
    parser = argparse.ArgumentParser(description="Crawler for recipe websites.")
    parser.add_argument('--save-to-db', action='store_true', help='Save scraped data to the database')
    args = parser.parse_args()

    main(args.save_to_db)