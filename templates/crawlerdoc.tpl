<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Botinfo - Finde-Mein-Rezept.de</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
        }
      	.divider {
    		border-top: 1px solid #28a745;
   			width: 50px;
    		margin: 0 auto;
		}
        .container {
            display: flex;
            flex-direction: column;
            width: 80%;
            max-width: 1200px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .header-image {
            position: relative;
            width: 100%;
            height: 150px;
            background-image: url('../images/cooking.jpg'); /* Verwende hier den Pfad zu deinem Bild */
            background-size: cover;
            background-position: center;
        }

        .header-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: 30px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        h1 {
            margin-top: 0;
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        h2, a {
            color: #2C3E50;
        }
        a { 
            text-decoration: none; 
            }

        p {
            font-size: 17px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            color: #888;
            font-size: 14px;
        }
      
              .info-box {

            padding: 15px;

            background-color: #e9f7f6;

            border-left: 4px solid #4BB5C1;

            margin-bottom: 20px;
               } 

        @media (max-width: 768px) {
            .container {
                width: 90%;
            }

            .header-text {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="container">
        <!-- Header mit Bild und Überschrift -->
        <div class="header-image">
            <div class="header-text">Kurzerklärung des Crawlers</div>
        </div>

        <!-- Inhaltsbereich -->
        <div class="content">
            <p>
                Diese Dokumentation beschreibt die Funktionsweise und Verwendung unseres Rezept-Crawlers. Der Crawler durchsucht das Web nach öffentlich zugänglichen Rezepten und speichert wesentliche Informationen in unserer Datenbank.
            </p>

            <h2>Installation</h2>
            <p>
                Um den Crawler zu verwenden, musst du sicherstellen, dass alle erforderlichen Python-Pakete installiert sind. Du kannst die Pakete mit folgendem Befehl installieren:
            </p>
            <div class="info-box">
                <code>pip install -r requirements.txt</code>
            </div>

            <h2>Abhängigkeiten</h2>
            <p>
                Der Crawler benötigt die folgenden Python-Pakete:
            </p>
            <ul>
                <li><strong>requests:</strong> Ermöglicht das Senden von HTTP-Anfragen und das Empfangen von Antworten</li>
                <li><strong>beautifulsoup4:</strong> Dient zum Parsen und Extrahieren von Daten aus HTML- und XML-Dateien</li>
                <li><strong>mysql-connector-python:</strong> Stellt eine Verbindung zur MySQL-Datenbank her und ermöglicht Datenbankoperationen</li>
                <li><strong>colorama:</strong> Fügt Farbunterstützung für Terminalausgaben hinzu</li>
                <li><strong>argparse:</strong> Erleichtert das Parsen von Kommandozeilenargumenten</li>
                <li><strong>logging:</strong> Bietet ein flexibles Framework für das Generieren von Protokollnachrichten</li>
            </ul>
            <p>
                Stelle sicher, dass diese Pakete in deiner <code>requirements.txt</code> Datei aufgeführt sind.
            </p>

            <h2>Voraussetzungen</h2>
            <p>
                Bevor du den Crawler ausführst, stelle sicher, dass die folgenden Voraussetzungen erfüllt sind:
            </p>
            <ul>
                <li>Python 3.6 oder höher</li>
                <li>Eine MySQL-Datenbank mit den erforderlichen Tabellen</li>
                <li>Eine <code>urls.json</code> Datei mit den Konfigurationsdetails der zu scrapenden Websites</li>
            </ul>

            <h2>Konfiguration</h2>
            <p>
                Die Konfiguration der zu scrapenden Websites erfolgt über die Datei <code>urls.json</code>. Diese Datei enthält eine Liste von Websites und deren spezifischen Selektoren für die verschiedenen Elemente eines Rezepts (Titel, Zutaten, Beschreibung, Bild).
            </p>
            <pre>
{
    "websites": [
        {
            "name": "example.com",
            "url": "https://www.example.com/recipes",
            "page_param": "?page=",
            "pages": 5,
            "selectors": {
                "recipe": ".recipe-link",
                "title": ".recipe-title",
                "ingredients": ".recipe-ingredients li",
                "description": ".recipe-description",
                "image": ".recipe-image img"
            },
            "type": "html",
            "source_id": 1
        }
    ]
}</pre>


            <h2>Verwendung</h2>
            <p>
                Der Crawler kann über die Kommandozeile mit verschiedenen Argumenten gestartet werden. Hier sind die verfügbaren Argumente:
            </p>
            <ul>
                <li><code>--debug</code>: Aktiviert den Debug-Modus. In diesem Modus werden keine Daten in die Datenbank gespeichert.</li>
                <li><code>--rate-limit</code>: Begrenzt die Anzahl der Anfragen pro Sekunde.</li>
                <li><code>--website</code>: Gibt eine spezifische Website an, die gecrawlt werden soll.</li>
                <li><code>--ignore-existing</code>: Ignoriert bestehende Rezepte in der Datenbank.</li>
                <li><code>--save-to-file</code>: Speichert Debug-Ergebnisse in einer Datei.</li>
                <li><code>--timeout</code>: Setzt das Timeout für HTTP-Anfragen in Sekunden (Standard: 10 Sekunden).</li>
                <li><code>--user-agent</code>: Setzt einen benutzerdefinierten User-Agent-Header.</li>
            </ul>

            <h2>Beispiel</h2>
            <p>
                Hier ist ein Beispiel, wie der Crawler mit verschiedenen Argumenten gestartet werden kann:
            </p>
            <div class="info-box">
                <code>python main.py --debug --rate-limit 2 --website "example.com" --ignore-existing</code>
            </div>

            <h2>Funktionen</h2>
            <p>
                Der Crawler besteht aus mehreren Modulen, die zusammenarbeiten, um Rezepte zu finden und zu speichern. Hier sind einige der wichtigsten Funktionen:
            </p>
            <ul>
                <li><code>main()</code>: Die Hauptfunktion, die den Crawler startet und die Argumente verarbeitet.</li>
                <li><code>scrap_recipe_overview()</code>: Extrahiert Rezept-URLs von einer Übersichtsseite.</li>
                <li><code>parse_recipe()</code>: Parst die Rezeptdetails von einer Rezeptseite.</li>
                <li><code>get_existing_recipes()</code>: Holt alle bestehenden Rezept-URLs aus der Datenbank.</li>
                <li><code>insert_recipe()</code>: Fügt ein neues Rezept in die Datenbank ein.</li>
                <li><code>send_telegram_notifications()</code>: Sendet Benachrichtigungen über Telegram.</li>
            </ul>

            <h2>Module</h2>
            <p>
                Der Crawler ist in mehrere Module unterteilt, die jeweils eine spezifische Aufgabe erfüllen:
            </p>
            <h3>main.py</h3>
            <p>
                Dies ist die Hauptdatei, die den Crawler startet und die Argumente verarbeitet. Sie enthält die <code>main()</code>-Funktion, die den gesamten Ablauf steuert.
            </p>
            <h3>scraper.py</h3>
            <p>
                Dieses Modul enthält die Logik zum Scrapen der Rezeptseiten. Die Funktion <code>scrap_recipe_overview()</code> extrahiert die Rezept-URLs von einer Übersichtsseite, während <code>parse_recipe()</code> die Details eines Rezepts parst.
            </p>
            <h3>database.py</h3>
            <p>
                Dieses Modul enthält die Funktionen zum Interagieren mit der Datenbank. Es stellt die Verbindung zur Datenbank her und enthält Funktionen wie <code>get_existing_recipes()</code> und <code>insert_recipe()</code>.
            </p>
            <h3>notification.py</h3>
            <p>
                Dieses Modul enthält die Logik zum Senden von Benachrichtigungen über Telegram. Die Funktion <code>send_telegram_notifications()</code> sendet eine Nachricht an eine vordefinierte Liste von Telegram-Chat-IDs.
            </p>

            
            <h2>Fehlerbehebung</h2>
            <p>
                Falls du auf Probleme stößt, überprüfe bitte die folgenden Punkte:
            </p>
            <ul>
                <li>Stelle sicher, dass alle erforderlichen Pakete installiert sind.</li>
                <li>Überprüfe die Konfiguration in der <code>urls.json</code>-Datei.</li>
                <li>Überprüfe die Logs auf Fehlermeldungen und stack traces.</li>
            </ul>
            <footer>
                &copy; 2024 Finde-Mein-Rezept.de
            </footer>
        </div>
    </div>