import time
import json
import database
import scraper
import logging
import argparse
import sys

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

def main(debug):
    start_time = time.time()
    try:
        if debug:
            print(f"{RED}╔═══════════════════════════════════════════════════════════════════╗")
            print(f"║ {YELLOW}⚠️  ATTENTION: Debug Mode Activated!  ⚠️{RED}                            ║")
            print(f"║                                                                   ║")
            print(f"║ {CYAN}In this mode, no data will be saved to the database.{RED}              ║")
            print(f"║ {CYAN}All discovered recipes will be displayed for viewing only.{RED}        ║")
            print(f"║ {CYAN}Existing recipes are NOT considered in Debug Mode.{RED}                ║")
            print(f"║                                                                   ║")
            print(f"║ {CYAN}Use --save-to-file to optionally save debug results to a file.{RED}    ║")
            print(f"║                                                                   ║")
            print(f"║ {GREEN}Happy testing and debugging! 🕵️‍♂️🔍{RED}                                ║")
            print(f"╚═══════════════════════════════════════════════════════════════════╝{RESET}")
        
        connection = database.create_connection()
        # Loading the website config urls.json
        with open("urls.json", 'r') as file:
            website_configs = json.load(file)
    
        if not debug:
            existing_recipes = database.get_existing_recipes()
            print(f"{RESET}{len(existing_recipes)} {GREEN} recipes have been loaded from the database..")
        else:
            existing_recipes = []
        for website in website_configs['websites']:
            print(f"{CYAN}Scraping the following page {RESET}{website['url']}")

            if 'pages' in website:
                for page_num in range(1, website['pages'] + 1):
                    page_url = f"{website['url']}{website['page_param']}{page_num}"
                    print(f"{CYAN}Scraping page number {RESET}{page_num} {CYAN}of {RESET}{website['pages']}")
                    scraper.scrap_recipe_overview(page_url, website, existing_recipes, debug)
                    # recipe_id = database.insert_recipe(connection, title, description, website['source_id'], page_url, image_url)
            else:
                scraper.scrap_recipe_overview(website["url"], website, existing_recipes, debug)
                
        


    except KeyboardInterrupt:
        print(f"{RED}Execution was terminated by user..{RESET}")

    finally:
        if connection.is_connected():
            connection.close()
            print(f"{RED}MySQL database connection has closed!")


        new_recipes = scraper.get_new_recipes_count()
        print(f"{GREEN}Number of new recipes found: {RESET}{new_recipes}")
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
    parser.add_argument('--debug', action='store_true', help='Enable debug mode')
    args = parser.parse_args()

    main(args.debug)