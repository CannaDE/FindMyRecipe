import mysql.connector
from mysql.connector import Error, pooling
from logger_config import setup_logger

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

_connection = None
logger = setup_logger(__name__)

# Verbindungspool erstellen
connection_pool = pooling.MySQLConnectionPool(
    pool_name="mypool",
    pool_size=8,  # Anzahl der Verbindungen im Pool
    pool_reset_session=True,
    host='localhost',
    database='rezept',
    user='root',
    password='',
    charset='utf8mb4',
    collation='utf8mb4_unicode_ci'
)

# Funktion, um eine Verbindung aus dem Pool zu erhalten 
def get_connection():
    try:
        connection = connection_pool.get_connection()
        if connection.is_connected():
            logger.info("MySQL Database connection successfully obtained from pool")
        return connection
    except Error as e:
        logger.error(f"Error getting connection from pool: {e}")
        return None

# Beispiel für die Verwendung der Verbindung
def get_existing_recipes():
    connection = get_connection()
    if connection is None:
        logger.error("MySQL Connection not available.")
        return set()
    cursor = connection.cursor()
    cursor.execute("SELECT url FROM recipes")
    results = cursor.fetchall()
    cursor.close()  # Cursor schließen
    connection.close()  # Verbindung zurück in den Pool geben
    return set([row[0] for row in results])

# Funktion, um ein Rezept in die Datenbank einzufügen
def insert_recipe(title, description, source_id, url, image_url):
    connection = get_connection()
    if connection is None:
        logger.error("MySQL Connection not available.")
        return None
    cursor = connection.cursor()
    query = "INSERT INTO recipes (title, description, source_id, url, image_url, duration) VALUES (%s, %s, %s, %s, %s, 0)"
    cursor.execute(query, (title, description, source_id, url, image_url))
    connection.commit()
    lastrowid = cursor.lastrowid  # ID des eingefügten Rezepts speichern
    cursor.close()  # Cursor schließen
    connection.close()  # Verbindung zurück in den Pool geben
    return lastrowid

# Funktion, um eine Zutat in die Datenbank einzufügen oder ihre ID zu holen
def get_or_create_ingredient(ingredient_name):
    connection = get_connection()
    if connection is None:
        logger.error("MySQL Connection not available.")
        return None
    cursor = connection.cursor()
    query = "SELECT id FROM ingredients WHERE name = %s"
    cursor.execute(query, (ingredient_name,))
    result = cursor.fetchone()
    
    if result:
        ingredient_id = result[0]  # Existierende Zutat-ID speichern
    else:
        query = "INSERT INTO ingredients (name) VALUES (%s)"
        cursor.execute(query, (ingredient_name,))
        connection.commit()
        ingredient_id = cursor.lastrowid  # Neue Zutat-ID speichern

    cursor.close()  # Cursor schließen
    connection.close()  # Verbindung zurück in den Pool geben
    return ingredient_id

# Funktion, um eine Zutat einem Rezept hinzuzufügen (MYSQL JOIN)
def insert_recipe_ingredient(recipe_id, ingredient_id):
    connection = get_connection()
    if connection is None:
        logger.error("MySQL Connection not available.")
        return None
    cursor = connection.cursor()
    query_check = "SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = %s AND ingredient_id = %s"
    cursor.execute(query_check, (recipe_id, ingredient_id))
    exists = cursor.fetchone()[0] > 0

    if not exists:
        query_insert = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (%s, %s)"
        cursor.execute(query_insert, (recipe_id, ingredient_id))
        connection.commit()

    cursor.close()  # Cursor schließen
    connection.close()  # Verbindung zurück in den Pool geben