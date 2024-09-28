<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zutaten auswählen und Ergebnisse anzeigen</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-color: #f4f4f4;
            overflow: hidden; /* Verhindert Scrollbars auf Body-Ebene */
        }

        /* Container für Inhalt */
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            height: 100vh; /* Höhe des Containers anpassen */
            max-width: 100vw; /* Beschränke die Breite auf den sichtbaren Bereich */
            box-sizing: border-box; /* Padding wird zur Breite addiert */
        }

        .panel {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Scrollbare Inhalte */
            scrollbar-width: thin; /* Dünne Scrollbars in Firefox */
            scrollbar-color: #888 #f4f4f4; /* Scrollbar-Farbe in Firefox */
            height: 100%; /* Höhe der Panels anpassen */
        }

        /* Styled Scrollbars für Chrome, Edge und Safari */
        .panel::-webkit-scrollbar {
            width: 10px;
        }

        .panel::-webkit-scrollbar-track {
            background: #f4f4f4;
        }

        .panel::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 5px;
            border: 3px solid #f4f4f4;
        }

        .left-panel {
            flex: 1;
            margin-right: 20px;
            max-height: 100%; /* Verhindert, dass der Inhalt über den Bildschirm ragt */
        }

        .right-panel {
            flex: 2;
            max-height: 100%; /* Scrollbarer Inhalt */
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .ingredient-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ingredient-list li {
            margin-bottom: 10px;
        }

        .ingredient-list label {
            font-size: 18px;
            cursor: pointer;
            color: #555;
        }

        .ingredient-list input[type="checkbox"] {
            margin-right: 10px;
        }

        .results {
            display: flex;
            flex-wrap: wrap; /* Erlaubt Zeilenumbruch */
            gap: 20px; /* Abstand zwischen den Rezepten */
            margin-top: 20px; /* Abstand zum Titel */
        }

        .recipe {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex; /* Flexbox für quadratische Anordnung */
            overflow: hidden; /* Verhindert Überlappungen */
            width: calc(50% - 10px); /* 2 Rezepte pro Zeile mit Abstand */
            max-height: 160px; /* Maximale Höhe des Rezepts */
        }

        .recipe img {
            width: 150px; /* Feste Breite für das Bild */
            height: 150px; /* Feste Höhe für das Bild */
            object-fit: cover; /* Bild passt in den Container */
        }

        .recipe-content {
            padding: 15px; /* Abstand um den Text */
            display: flex;
            flex-direction: column; /* Textinhalt untereinander */
            justify-content: center; /* Zentriert den Inhalt */
            flex: 1; /* Füllt den verfügbaren Platz */
        }

        .recipe h2 {
            font-size: 20px;
            margin: 10px 0;
            color: #2C3E50;
        }

        .recipe p {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .recipe .source {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }

        .recipe a {
            display: inline-block;
            background-color: #28a745;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }

        .recipe a:hover {
            background-color: #218838;
        }

        .header-image {
            position: relative;
            margin: -20px;
            margin-bottom: 20px;
            height: 50px;
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
            font-size: 25px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .right-panel {
                width: 100%;
            }

            .recipe {
                width: calc(100% - 10px); /* Vollbreite für mobile Ansicht */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Linke Seite: Zutaten auswählen -->
        <div class="panel left-panel">
            <div class="header-image">
                <div class="header-text">Zutaten wählen</div>
            </div>
            <ul class="ingredient-list">
                <li><label><input type="checkbox">Tomaten</label></li>
                <li><label><input type="checkbox">Käse</label></li>
                <li><label><input type="checkbox">Pasta</label></li>
                <li><label><input type="checkbox">Hühnchen</label></li>
                <li><label><input type="checkbox">Zwiebeln</label></li>
                <li><label><input type="checkbox">Knoblauch</label></li>
            </ul>
        </div>

        <!-- Rechte Seite: Ergebnisse -->
        <div class="panel right-panel">
            <div class="header-image">
                <div class="header-text">Rezeptergebnisse</div>
            </div>
            <div class="results">
                <!-- Rezept 1 -->
                <div class="recipe">
                    <img src="https://via.placeholder.com/150" alt="Rezeptbild">
                    <div class="recipe-content">
                        <h2>Spaghetti Bolognese</h2>
                        <p>Ein klassisches Rezept mit Tomaten, Zwiebeln und Knoblauch.</p>
                        <div class="source">Quelle: kochbar.de</div>
                        <a href="#">Zum Rezept</a>
                    </div>
                </div>
                <!-- Rezept 2 -->
                <div class="recipe">
                    <img src="https://via.placeholder.com/150" alt="Rezeptbild">
                    <div class="recipe-content">
                        <h2>Hähnchen-Pasta</h2>
                        <p>Leckere Pasta mit Hühnchen, Knoblauch und Zwiebeln.</p>
                        <div class="source">Quelle: kochbar.de</div>
                        <a href="#">Zum Rezept</a>
                    </div>
                </div>
                <!-- Rezept 3 -->
                <div class="recipe">
                    <img src="https://via.placeholder.com/150" alt="Rezeptbild">
                    <div class="recipe-content">
                        <h2>Caprese-Salat</h2>
                        <p>Frischer Salat mit Tomaten, Käse und Basilikum.</p>
                        <div class="source">Quelle: kochbar.de</div>
                        <a href="#">Zum Rezept</a>
                    </div>
                </div>
                <!-- Rezept 4 -->
                <div class="recipe">
                    <img src="https://via.placeholder.com/150" alt="Rezeptbild">
                    <div class="recipe-content">
                        <h2>Pasta Carbonara</h2>
                        <p>Traditionelles italienisches Gericht mit Eiern und Speck.</p>
                        <div class="source">Quelle: kochbar.de</div>
                        <a href="#">Zum Rezept</a>
                    </div>
                </div>
                <!-- Rezept 5 -->
                <div class="recipe">
                    <img src="https://via.placeholder.com/150" alt="Rezeptbild">
                    <div class="recipe-content">
                        <h2>Lasagne</h2>
                        <p>Schichtgericht mit Hackfleisch und Bechamelsauce.</p>
                        <div class="source">Quelle: kochbar.de</div>
                        <a href="#">Zum Rezept</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>