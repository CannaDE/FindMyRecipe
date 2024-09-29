import requests;
from colorama import Fore, Style, init

# Initialize colorama
init(autoreset=True)

RED = Fore.RED
GREEN = Fore.GREEN
YELLOW = Fore.YELLOW
BLUE = Fore.BLUE
CYAN = Fore.CYAN
RESET = Style.RESET_ALL

TELEGRAM_TOKEN = "8055605849:AAFoZIFir5qT5i-934FzIKy06aL8G3S2xpI"
TELEGRAM_CHAT_IDS = ["215730917"]

def send_telegram_notifications(message):    
    url = f"https://api.telegram.org/bot{TELEGRAM_TOKEN}/sendMessage"
    
    for chat_id in TELEGRAM_CHAT_IDS:
        payload = {
            'chat_id': chat_id,
            'text': message
        }
        
        try:
            response = requests.post(url, data=payload)
            if response.status_code != 200:
                print(f"{RED}Failed to send Telegram notification to chat ID {chat_id}: {response.text}")
        except Exception as e:
            print(f"{RED}Failed to send Telegram notification to chat ID {chat_id}: {e}")

def buildCrawlerSuccessMessage(new_recipes, elapsed_time):
    hours, rem = divmod(elapsed_time, 3600)
    minutes, seconds = divmod(rem, 60)
    runtime = f"{int(hours)} Stunden, {int(minutes)} Minuten & {int(seconds)} Sekunden" if hours > 0 else f"{int(minutes)} Minuten und {int(seconds)} Sekunden" if minutes > 0 else f"{int(seconds)} Sekunden"
    message = f"""🤖 Crawler-Ausführung erfolgreich abgeschlossen! 🎉

📊 Ergebnisse:
🍳 Frisch gefundene Rezepte: {new_recipes}
⏱️ Gesamtlaufzeit: {runtime}"""

    return message

def buildCrawlerUserExitedMessage(new_recipes, elapsed_time):
    hours, rem = divmod(elapsed_time, 3600)
    minutes, seconds = divmod(rem, 60)
    runtime = f"{int(hours)} Stunden, {int(minutes)} Minuten & {int(seconds)} Sekunden" if hours > 0 else f"{int(minutes)} Minuten und {int(seconds)} Sekunden" if minutes > 0 else f"{int(seconds)} Sekunden"
    
    message = f"""🛑 Crawler-Ausführung vom Benutzer beendet! ⚠️

📊 Zwischenergebnisse:
🍳 Bisher gefundene Rezepte: {new_recipes}
⏱️ Laufzeit bis zur Unterbrechung: {runtime}

ℹ️ Der Crawler wurde manuell gestoppt. Einige Rezepte könnten unvollständig sein."""

    return message
