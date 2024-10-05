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
        <p class="text-center text-gray-600 mb-4">
            <?php
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $username = $_SERVER['PHP_AUTH_USER'];
                echo "Da habe ich dich doch erwischt, " . htmlspecialchars($username);
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
    </div>
</body>
</html>