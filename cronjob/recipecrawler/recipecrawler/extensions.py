from scrapy import signals
import logging
import time
from notifications import Notification
from recipecrawler.logger_config import setup_logger
from scrapy.exceptions import NotConfigured

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

class CrawlerTimingExtension:
    logger = setup_logger(__name__)

    def __init__(self):
        self.notification = Notification()
        self.start_time = None

    @classmethod
    def from_crawler(cls, crawler):
        ext = cls()
        crawler.signals.connect(ext.spider_opened, signal=signals.spider_opened)
        crawler.signals.connect(ext.spider_closed, signal=signals.spider_closed)
        return ext

    def spider_opened(self, spider):
        self.start_time = time.time()
        spider.logger.info(f"{CYAN}* * * Recipe Crawler Launching * * *{RESET}")
        spider.logger.info(f"{GREEN}> Starting the hunt for tasty new recipes{RESET}")
        spider.logger.info(f"{YELLOW}> Crawler is now processing. This might take a while.{RESET}")
        spider.logger.info(f"{GREEN}> Let's discover some culinary treasures! {RESET}")
        spider.logger.info(f"{YELLOW}> Duration depends on the number of pages to analyze.{RESET}")

    def spider_closed(self, spider, reason):
        end_time = time.time()
        elapsed_time = end_time - self.start_time
        end_time = time.time()
        elapsed_time = end_time - self.start_time
        hours, rem = divmod(elapsed_time, 3600)
        minutes, seconds = divmod(rem, 60)

        if hours > 0:
            spider.logger.info(f"Recipes found: {spider.newRecipesCounter}")   
            spider.logger.info(f"Recipes skipped: {spider.skippedRecipesCounter}")
            spider.logger.info(f"Spider closed: {reason} Runtime: {int(hours)} hours, {int(minutes)} minutes & {int(seconds)} seconds")
        elif minutes > 0:
            spider.logger.info(f"Recipes found: {spider.newRecipesCounter}")
            spider.logger.info(f"Recipes skipped: {spider.skippedRecipesCounter}")
            spider.logger.info(f"Spider closed: {reason} Runtime: {int(minutes)} minutes and {int(seconds)} seconds")    
        else:
            spider.logger.info(f"Recipes found: {spider.newRecipesCounter}")
            spider.logger.info(f"Recipes skipped: {spider.skippedRecipesCounter}")
            spider.logger.info(f"Spider closed: {reason} Runtime: {int(seconds)} seconds")
        
        if reason == 'finished':
            if spider.crawler.settings.getbool('TELEGRAM_NOTIFICATION', False):
                self.notification.send_telegram_notification(self.buildCrawlerSuccessMessage(elapsed_time, spider))
        elif reason == 'cancelled':
            if spider.crawler.settings.getbool('TELEGRAM_NOTIFICATION', False):
                self.notification.send_telegram_notification(self.buildCrawlerUserExitedMessage(elapsed_time, spider))
        elif reason == 'shutdown':
            if spider.crawler.settings.getbool('TELEGRAM_NOTIFICATION', False):
                self.notification.send_telegram_notification(self.buildCrawlerUserExitedMessage(elapsed_time, spider))

    def buildCrawlerSuccessMessage(self, elapsed_time, spider):
        hours, rem = divmod(elapsed_time, 3600)
        minutes, seconds = divmod(rem, 60)
        runtime = f"{int(hours)} Stunden, {int(minutes)} Minuten & {int(seconds)} Sekunden" if hours > 0 else f"{int(minutes)} Minuten und {int(seconds)} Sekunden" if minutes > 0 else f"{int(seconds)} Sekunden"
        message = f"""🤖 Crawler-Ausführung erfolgreich abgeschlossen! 🎉

    📊 Ergebnisse:
    🍳 Gefundene Rezepte: {spider.foundedRecipesCounter} 
    ⏭️ Übersprungen: {spider.skippedRecipesCounter}
    ➕ Eingefügt: {spider.newRecipesCounter}
    ⏱️ Gesamtlaufzeit: {runtime}"""

        return message
    
    def buildCrawlerUserExitedMessage(self, elapsed_time, spider):
        hours, rem = divmod(elapsed_time, 3600)
        minutes, seconds = divmod(rem, 60)
        runtime = f"{int(hours)} Stunden, {int(minutes)} Minuten & {int(seconds)} Sekunden" if hours > 0 else f"{int(minutes)} Minuten und {int(seconds)} Sekunden" if minutes > 0 else f"{int(seconds)} Sekunden"
        message = f"""🛑 Crawler-Ausführung vom Benutzer beendet! ⚠️

    📊 Zwischenergebnisse:
    🍳 Gefundene Rezepte: {spider.foundedRecipesCounter} 
    ⏭️ Übersprungen: {spider.skippedRecipesCounter}
    ➕ Eingefügt: {spider.newRecipesCounter}
    ⏱️ Laufzeit bis zur Unterbrechung: {runtime} 

    ℹ️ Der Crawler wurde manuell gestoppt. Einige Rezepte könnten unvollständig sein.""" 

        return message
