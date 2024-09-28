<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Finde-Mein-Rezept.de - Rezeptsuchmaschinen-Datenbank für sortierte Gerichte nach vorhandenen Zutaten"/>
    <meta name="keywords" content="waskocheich, finde mein rezept, finde dein rezept, zutatensuche, rezeptsuche, suchmaschine, datenbank"/>
    <meta name="author" content="Finde-Mein-Rezept.de" />
    <meta name="copyright" content="Finde-Mein-Rezept.de" />
    <meta name="robots" content="follow"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Finde-Mein-Rezept.de | Bald verfügbar</title>
    <link rel="stylesheet" href="style/maintenance.css">
    
    <!-- Google Analytics Tag -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
</head>
<body>
<div class="container">
        <div class="image-container" id="image-container"></div>
        <div class="content-container">
            <h1>Finde-Mein-Rezept.de</h1>
            <div class="divider"></div>
            <p>Die Rezeptsuche wird bald verfügbar sein! Trage gerne deine E-Mail-Adresse ein, um benachrichtigt zu werden, sobald wir online sind.</p>
            <div id="message"></div>
            <form class="email-form" id="notifyForm" action="" method="post">
                <input type="email" name="email" id="email" placeholder="Deine E-Mail-Adresse" required disabled>
                <button type="submit" disabled>Benachrichtigen lassen</button>
            </form>

             <footer>
                &copy; 2024 Finde-Mein-Rezept.de
            </footer>
        </div>
    </div>


    <script src="maintenance.js"></script>
</body>
</html>