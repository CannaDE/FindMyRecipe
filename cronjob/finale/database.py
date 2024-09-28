import mysql.connector
from mysql.connector import Error

RED = "\033[31m"
GREEN = "\033[32m"
YELLOW = "\033[33m"
BLUE = "\033[34m"
CYAN = "\033[36m"
RESET = "\033[0m"

_connection = None

# Funktion, um die MySQL-Verbindung herzustellen
def create_connection():
    global _connection
    if _connection is None:
        try:
            _connection = mysql.connector.connect(
                host='localhost',
                database='rezept',
                user='root',
                password='',
                charset='utf8mb4',  # WICHTIG: Zeichensatz für die Verbindung setzen
                collation='utf8mb4_unicode_ci'
            )
            if _connection.is_connected():
                print(f"{GREEN}MySQL Database connection successfully")
        except Error as e:
            print(f"{RED}MySQL Database connection error: {RESET}{e}")
            _connection = None
    return _connection

# Funktion, um die bestehende Verbindung zu erhalten
def get_connection():
    if _connection is None:
        return create_connection()
    return _connection

# Funktion, um alle Rezept-URLs aus der Datenbank zu holen
def get_existing_recipes():
    connection = get_connection()
    cursor = connection.cursor()
    cursor.execute("SELECT url FROM recipes")
    results = cursor.fetchall()
    return set([row[0] for row in results])  # Rückgabe eines Sets von URLs (schnellere Abfragen)

# Funktion, um ein Rezept in die Datenbank einzufügen
def insert_recipe(title, description, source_id, url, image_url):
    connection = get_connection()
    cursor = connection.cursor()
    query = "INSERT INTO recipes (title, description, source_id, url, image_url, duration) VALUES (%s, %s, %s, %s, %s, 0)"
    cursor.execute(query, (title, description, source_id, url, image_url))
    connection.commit()
    return cursor.lastrowid  # Rückgabe der ID des eingefügten Rezepts

# Funktion, um eine Zutat in die Datenbank einzufügen oder ihre ID zu holen
def get_or_create_ingredient(ingredient_name):
    connection = get_connection()
    cursor = connection.cursor()
    query = "SELECT id FROM ingredients WHERE name = %s"
    cursor.execute(query, (ingredient_name,))
    result = cursor.fetchone()
    
    if result:
        return result[0]  # Rückgabe der existierenden Zutat-ID
    else:
        query = "INSERT INTO ingredients (name) VALUES (%s)"
        cursor.execute(query, (ingredient_name,))
        connection.commit()
        return cursor.lastrowid  # Rückgabe der neuen Zutat-ID

# Funktion, um eine Zutat einem Rezept hinzuzufügen (MYSQL JOIN)
def insert_recipe_ingredient(recipe_id, ingredient_id):
    connection = get_connection()
    cursor = connection.cursor()
    query_check = "SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = %s AND ingredient_id = %s"
    cursor.execute(query_check, (recipe_id, ingredient_id))
    exists = cursor.fetchone()[0] > 0

    if not exists:
        query_insert = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (%s, %s)"
        cursor.execute(query_insert, (recipe_id, ingredient_id))
        connection.commit()