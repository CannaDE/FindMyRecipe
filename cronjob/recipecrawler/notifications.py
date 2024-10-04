import requests

class Notification:
    def __init__(self):
        self.telegram_token = "7831049878:AAGb8DGiZAV7JgtRZyseR__13mutlvl797Q"
        self.telegram_chat_ids = ["215730917"]

    def send_telegram_notification(self, message):
        url = f"https://api.telegram.org/bot{self.telegram_token}/sendMessage"
        for chat_id in self.telegram_chat_ids:
            payload = {
                'chat_id': chat_id,
                'text': message
            }
            
            try:
                response = requests.post(url, data=payload)
                if response.status_code != 200:
                    print(f"Failed to send Telegram notification to chat ID {chat_id}: {response.text}")
            except Exception as e:
                print(f"Failed to send Telegram notification to chat ID {chat_id}: {e}")

