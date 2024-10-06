import time
import json
import database
import scraper
import logging
import argparse
from logger_config import setup_logger
import notification
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

# Configure logging
logger = setup_logger(__name__)

def scrape_page(page_url, website, existing_recipes, debug, save_to_file, user_agent, timeout):
    thread_id = threading.get_ident()
    logger.info(f"Thread {thread_id}: Scraping page {page_url}")
    connection = database.get_connection()
    scraper.scrap_recipe_overview(connection,page_url, website, existing_recipes, debug, save_to_file, user_agent, timeout)

def scrape_website(website, existing_recipes, debug, save_to_file, user_agent, timeout, rate_limit_interval, max_threads):
    if 'pages' in website:
        page_urls = [f"{website['url']}{website['page_param']}{page_num}" for page_num in range(1, website['pages'] + 1)]
        
        if debug:
            max_threads = 1
        
        with ThreadPoolExecutor(max_workers=max_threads) as executor:
            futures = [executor.submit(scrape_page, page_url, website, existing_recipes, debug, save_to_file, user_agent, timeout) for page_url in page_urls]
            try:
                for future in as_completed(futures):
                    try:
                        future.result()
                    except Exception as e:
                        logger.error(f"Error occurred during scraping: {e}")
                    if rate_limit_interval:
                        time.sleep(rate_limit_interval)
            except KeyboardInterrupt:
                logger.info("KeyboardInterrupt received, shutting down...")
                executor.shutdown(wait=False, cancel_futures=True)
                raise
    else:
        scrape_page(website["url"], website, existing_recipes, debug, save_to_file, user_agent, timeout)

def main(debug, website_name, save_to_file, user_agent, timeout, rate_limit, ignore_existing, max_threads):
    start_time = time.time()
    try:
        if debug:
            print(f"{RED}╔═══════════════════════════════════════════════════════════════════╗")
            print(f"{RED}║ {YELLOW}⚠️  ATTENTION: Debug Mode Activated!  ⚠️{RED}                            ║")
            print(f"{RED}║                                                                   ║")
            print(f"{RED}║ {CYAN}In this mode, no data will be saved to the database.{RED}              ║")
            print(f"{RED}║ {CYAN}All discovered recipes will be displayed for viewing only.{RED}        ║")
            print(f"{RED}║ {CYAN}Existing recipes are NOT considered in Debug Mode.{RED}                ║")
            print(f"{RED}║                                                                   ║")
            print(f"{RED}║ {CYAN}Use --save-to-file to optionally save debug results to a file.{RED}    ║")
            print(f"{RED}║                                                                   ║")
            print(f"{RED}║ {GREEN}Happy testing and debugging! 🕵️‍♂️🔍{RED}                                ║")
            print(f"{RED}╚═══════════════════════════════════════════════════════════════════╝{RESET}")
        else:
            print(f"\n{CYAN}* * * Recipe Crawler Launching * * *{RESET}")
            print(f"{GREEN}> Starting the hunt for tasty new recipes{RESET}")
            print(f"{YELLOW}> Crawler is now processing. This might take a while.{RESET}")
            print(f"{YELLOW}> Duration depends on the number of pages to analyze.{RESET}")
            print(f"{GREEN}> Let's discover some culinary treasures! 🥘🔍{RESET}\n")
            
        #connection = database.create_connection()
        # Loading the website config urls.json
        with open("urls.json", 'r') as file:
            website_configs = json.load(file)
    
        if not debug and not ignore_existing:
            existing_recipes = database.get_existing_recipes()
            logger.info(f"{len(existing_recipes)} recipes have been loaded from the database.")
        else:
            existing_recipes = []

        # Filter websites based on the optional website_name parameter
        websites_to_scrape = website_configs['websites']
        if website_name:
            websites_to_scrape = [website for website in websites_to_scrape if website['name'] == website_name]
            if not websites_to_scrape:
                logger.error(f"No website found with the name: {website_name}. Please check the configuration.")
                return

        rate_limit_interval = 1.0 / rate_limit if rate_limit else None

        for website in websites_to_scrape:
            scrape_website(website, existing_recipes, debug, save_to_file, user_agent, timeout, rate_limit_interval, max_threads)

        neue_rezepte = scraper.get_new_recipes_count()
        end_time = time.time()
        elapsed_time = end_time - start_time

        if not debug:
            message = notification.buildCrawlerSuccessMessage(neue_rezepte, elapsed_time)
            notification.send_telegram_notifications(message)
                
    except KeyboardInterrupt:
        logger.error(f"Execution was terminated by user.")
        new_recipes = scraper.get_new_recipes_count()
        end_time = time.time()
        elapsed_time = end_time - start_time

        if not debug:
            message = notification.buildCrawlerUserExitedMessage(new_recipes, elapsed_time)
            notification.send_telegram_notifications(message)

    finally:
        
        new_recipes = scraper.get_new_recipes_count()
        logger.info(f"Number of new recipes found: {new_recipes}")
        end_time = time.time()
        elapsed_time = end_time - start_time
        hours, rem = divmod(elapsed_time, 3600)
        minutes, seconds = divmod(rem, 60)

        if hours > 0:
            logger.info(f"Runtime of the crawler: {int(hours)} hours, {int(minutes)} minutes & {int(seconds)} seconds")
        elif minutes > 0:
            logger.info(f"Runtime of the crawler: {int(minutes)} minutes and {int(seconds)} seconds")
        else:
            logger.info(f"Runtime of the crawler: {int(seconds)} seconds")

if __name__ == '__main__': 
    parser = argparse.ArgumentParser(description="Crawler for recipe websites.")
    parser.add_argument('--debug', action='store_true', help='Enable debug mode')
    parser.add_argument('--rate-limit', type=int, help='Limit the number of requests per second')
    parser.add_argument('--website', type=str, help='Specify a website to scrape')
    parser.add_argument('--ignore-existing', action='store_true', help='Ignore existing recipes')
    parser.add_argument('--save-to-file', action='store_true', help='Save debug results to a file')
    parser.add_argument('--timeout', type=int, default=10, help='Timeout for HTTP requests in seconds')
    parser.add_argument('--user-agent', type=str, default="FindMyRecipeBot/1.0 (+https://finde-mein-rezept.de/botinfo)", help='Set a custom User-Agent header')
    parser.add_argument('--max-threads', type=int, default=5, help='Maximum number of threads to use')
    args = parser.parse_args()

    main(args.debug, args.website, args.save_to_file, args.user_agent, args.timeout, args.rate_limit, args.ignore_existing, args.max_threads)