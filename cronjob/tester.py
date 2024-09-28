import requests
from bs4 import BeautifulSoup

# URL der Rezeptseite
url = 'https://www.gutekueche.de/apfelkuechle-rezept-5084'

# HTTP-Header, um wie ein legitimer Browser zu erscheinen
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
}

# Anfrage an die Webseite
response = requests.get(url, headers=headers)

# Überprüfen, ob die Anfrage erfolgreich war
if response.status_code == 200:
    # Inhalt der Seite mit BeautifulSoup parsen
    soup = BeautifulSoup(response.content, 'html.parser')

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
                    ingredient = columns[1].get_text(strip=True)  # Name der Zutat
                    print(f"{amount} - {ingredient}")
    else:
        print("Keine Zutaten-Tabellen gefunden.")
else:
    print(f"Fehler beim Abrufen der Seite: {response.status_code}")
