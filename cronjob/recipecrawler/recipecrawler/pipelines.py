# Define your item pipelines here
#
# Don't forget to add your pipeline to the ITEM_PIPELINES setting
# See: https://docs.scrapy.org/en/latest/topics/item-pipeline.html


# useful for handling different item types with a single interface
from itemadapter import ItemAdapter
from mysql.connector import pooling, Error
from urllib.parse import urlparse
import logging
from recipecrawler.logger_config import setup_logger


class RecipecrawlerPipeline:
    def process_item(self, item, spider):
        return item
    

class MySQLPipeline:
    logger = setup_logger(__name__)

    def __init__(self):
        self.connection_pool = pooling.MySQLConnectionPool(
            pool_name="mypool",
            pool_size=26,
            pool_reset_session=True,
            host='localhost',
            database='rezept',
            user='root',
            password='',
            charset='utf8mb4',
            collation='utf8mb4_unicode_ci'
        )
    
    def open_spider(self, spider):
        self.existsRecipes = self.get_exists_recipes()
        self.spider = spider
        self.logger.info("[MySQLPipeline] open_spider")
    
    def close_spider(self, spider):
        spider.logger.info("[MySQLPipeline] close_spider")
    
    def get_connection(self):
        try:
            connection = self.connection_pool.get_connection()
            return connection
        except Error as e:
            print(f"Error getting connection from pool: {e}")
            return None
    
    def get_exists_recipes(self):
        connection = self.get_connection()
        if connection is None:
            print("MySQL Connection not available.")
            return
        cursor = connection.cursor()
        try:
            cursor.execute("SELECT url FROM recipes")
            return [row[0] for row in cursor.fetchall()]
        except Error as e:
            print(f"Error fetching existing recipes: {e}")
            return []
        finally:
            cursor.close()
            connection.close()

    def get_or_create_ingredient_id(self, ingredient_name):
        connection = self.get_connection()
        if connection is None:
            self.spider.logger.error("MySQL Connection not available.")
            return
        cursor = connection.cursor()
        try:
            cursor.execute("SELECT id FROM fmr_basic_ingredients WHERE name LIKE %s", (ingredient_name,))
            result = cursor.fetchone()
            if result:
                ingredient_id = result[0]
            else:
                cursor.execute("INSERT INTO fmr_basic_ingredients (name) VALUES (%s)", (ingredient_name,))
                connection.commit()
                ingredient_id = cursor.lastrowid

            return ingredient_id
        except Error as e:
            print(f"Error getting or creating ingredient ID: {e}")
            return None
        finally:
            cursor.close()
            connection.close()

    def get_or_create_source_id(self, url):
        connection = self.get_connection()
        if connection is None:
            self.spider.logger.error("MySQL Connection not available.")
            return
        cursor = connection.cursor()
        try:
            parsed_url = urlparse(url)
            full_url = f"{parsed_url.scheme}://{parsed_url.netloc}"

            cursor.execute("SELECT id FROM sources WHERE url = %s", (full_url,))
            result = cursor.fetchone()
            if result:
                source_id = result[0]
            else:
                parsed_url = urlparse(full_url)
                domain = parsed_url.netloc
                name = domain.split('.')[-2]  # Nimmt den Teil vor der Top-Level-Domain
                cursor.execute("INSERT INTO sources (url, name) VALUES (%s, %s)", (full_url, name))
                connection.commit()
                source_id = cursor.lastrowid
                self.spider.newRecipesCounter += 1

            return source_id
        except Error as e:
            print(f"Error getting or creating source ID: {e}")
            return None
        finally:
            cursor.close()
            connection.close()
    
    def insert_recipe_ingredient(self,recipe_id, ingredient_id):
        connection = self.get_connection()
        if connection is None:
            print("MySQL Connection not available.")
            return
        cursor = connection.cursor()

        try:
            query_check = "SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = %s AND ingredient_id = %s"
            cursor.execute(query_check, (recipe_id, ingredient_id))
            exists = cursor.fetchone()[0] > 0

            if not exists:
                query_insert = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (%s, %s)"
                cursor.execute(query_insert, (recipe_id, ingredient_id))
                connection.commit()

        except Error as e:
            self.spider.logger.error(f"Error insert recipe ingredient links: {e}")
            return []
        finally:
            cursor.close()
            connection.close()
    
    def process_item(self, item, spider):
        
        connection = self.get_connection()
        if connection is None:
            print("MySQL Connection not available.")
            return item
        cursor = connection.cursor()

        try:
            
            # skip existing recipes
            if item['url'] in self.existsRecipes:
                spider.skippedRecipesCounter += 1
                return item
            
            
            source_id = self.get_or_create_source_id(item['url'])

            query = "INSERT INTO recipes (title, description, source_id, url, image_url, duration) VALUES (%s, %s, %s, %s, %s, 0)"
            cursor.execute(query, (item['title'], item['description'], source_id, item['url'], item['image']))
            connection.commit()
            recipe_id = cursor.lastrowid
            spider.newRecipesCounter += 1

            for ingredient in item['ingredients']:
                ingredient_id = self.get_or_create_ingredient_id(ingredient)
                self.insert_recipe_ingredient(recipe_id, ingredient_id)
                

            # Ausgabe des Zwischenstands

            # Hier kannst du den Code zum Einfügen des Rezepts hinzufügen
            # Beispiel:
            # query = "INSERT INTO recipes (title, description, url, image_url) VALUES (%s, %s, %s, %s)"
            # cursor.execute(query, (item['title'], item['description'], item['url'], item['image']))
            # connection.commit()

        except Error as e:
           print(f"Error processing recipe: {e}")
        finally:
            cursor.close()
            connection.close()

        return item
    
