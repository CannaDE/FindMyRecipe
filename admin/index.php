<?php
    function sendTelegramNotification($message) {
        $telegramToken = "7831049878:AAGb8DGiZAV7JgtRZyseR__13mutlvl797Q";
        $telegramChatIds = ["215730917"];
        $url = "https://api.telegram.org/bot" . $telegramToken . "/sendMessage";
    
        foreach ($telegramChatIds as $chatId) {
            $payload = [
                'chat_id' => $chatId,
                'text' => $message
            ];
    
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($payload),
                ],
            ];
    
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
    
            if ($result === FALSE) {
                echo "Failed to send Telegram notification to chat ID $chatId\n";
            } else {
                $response = json_decode($result, true);
                if (!$response['ok']) {
                    echo "Failed to send Telegram notification to chat ID $chatId: " . $response['description'] . "\n";
                }
            }
        }
    }

    function getUserInfo() {
        // Relevante Header-Informationen
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unbekannt';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unbekannt';

        // Whois-Abfrage für die IP-Adresse
        $whoisInfo = getWhoisInfo($ipAddress);

        return [
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'whois_info' => $whoisInfo
        ];
    }

    function getWhoisInfo($ip) {
        // Verwende eine externe API, um Whois-Informationen zu erhalten
        echo $ip;
        $apiUrl = "https://ipinfo.io/{$ip}/json?token=44f6aadeaf25f0";
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);
        if ($data) {
            return [
                'country' => $data['country'] ?? 'Unbekannt',
                'region' => $data['region'] ?? 'Unbekannt',
                'city' => $data['city'] ?? 'Unbekannt',
                'isp' => $data['org'] ?? 'Unbekannt'
            ];
        }

        return [
            'country' => 'Unbekannt',
            'region' => 'Unbekannt',
            'city' => 'Unbekannt',
            'isp' => 'Unbekannt'
        ];
    }
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zutaten-Verwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-3xl font-bold mb-3 text-center text-gray-800">Zutaten-Verwaltung</h1>
        <p class="welcome easter-egg text-center text-gray-600 mb-4" id="easterEgg">
            <?php
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $username = $_SERVER['PHP_AUTH_USER'];
                echo "Da habe ich dich doch erwischt, " . htmlspecialchars($username);
                $userInfo = getUserInfo();
                $message = "⚠️ Neue Aktivität im Backend erkannt! ⚠️\n
👤 Nutzer: " . $username . "
🖥️ User-Agent: " . $userInfo['user_agent'] . "
🌐 IP-Adresse: " . $userInfo['ip_address'] . "
🗺️ Standort: " . $userInfo['whois_info']['country'] . "
🏞️ Region: " . $userInfo['whois_info']['region'] . "
🏙️ Stadt: " . $userInfo['whois_info']['city'] . "
🏢 ISP: " . $userInfo['whois_info']['isp'] . "
";
                sendTelegramNotification($message);
            } else {
                echo "Kein Benutzer angemeldet.";
            }
            ?>
        </p>
        <div class="space-y-4">
            <a href="manage_ingredients.php" class="block w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300 text-center">
                Zutaten verwalten
            </a>
            <a href="deduplicate_ingredients.php" class="block w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300 text-center">
                Zutaten deduplizieren
            </a>

            <a href="duplicates_ingredients.php" class="block w-full py-2 px-4g bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300 text-center">
                Doppelte Zutaten zusammenführen
            </a>
            <hr class="divider"/>
            <a href="manage_websites.php" class="block w-full py-2 px-4 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition duration-300 text-center">
                websites.yaml
            </a>
            <a href="crawler_log.php" class="block w-full py-2 px-4 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition duration-300 text-center">
                crawler.log
            </a>
        </div>
        <hr />
        <p class="text-center text-gray-600 mb-4 text-xs mt-3">
            branch: development<br />
            deploy date: DATUM<br />
            commit: aaa89d022e119ebe23478079f227686e76e0870c<br />
        </p>

    </style>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let inputSequence = '';
            const secretCode = 'larsstinkt'; // Der geheime String, der eingegeben werden muss

            document.addEventListener('keydown', function(event) {
                inputSequence += event.key.toLowerCase(); // Füge die gedrückte Taste zur Sequenz hinzu
                if (inputSequence.includes(secretCode)) {
                    document.getElementById('easterEgg').innerHTML = "Woher weißt du das? 🤔🧐"; // Zeige die geheime Nachricht an
                    inputSequence = ''; // Setze die Eingabesequenz zurück
                }
                // Begrenze die Länge der Eingabesequenz, um Speicherüberlauf zu vermeiden
                if (inputSequence.length > secretCode.length) {
                    inputSequence = inputSequence.slice(-secretCode.length);
                }
            });
        });
    </script>
</body>
</html>