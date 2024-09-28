import re

def clean_ingredient_name(ingredient_text):
    cleaned_text = re.sub(r'\d+.*?(\s|$)', '', ingredient_text)  # Entfernt Zahlen und alles danach
    cleaned_text = re.sub(r'\d+\s*[gml]|(?::g|ml|Esslöffel|Prise|Kopf|etwas|nach Geschmack|nach Bedarf|etwas|½|Bund)', '', cleaned_text)  # Entfernt Mengenangaben
    cleaned_text = re.sub(r'\s*/.*$', '', cleaned_text)  # Entfernt alles nach dem Schrägstrich
    cleaned_text = re.sub(r'\(.*?\)', '', cleaned_text)  # Entfernt alles in Klammern
    cleaned_text = re.sub(r'\w+:\s*', '', cleaned_text) # Entfernt Wörter, die mit einem Doppelpunkt enden
    cleaned_text = cleaned_text.replace('\n', ' ').strip()
    return cleaned_text.strip()