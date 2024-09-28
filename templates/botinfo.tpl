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
            <div class="header-text">{$pageTitle} - {PAGE_TITLE}</div>
        </div>

        <!-- Inhaltsbereich -->
        <div class="content">
            <p>
             Auf nachfolgender Seite möchten wir dir erklären, wie unser Bot arbeitet, welche Daten er sammelt und wie wir sicherstellen, dass die ursprünglichen Inhalte respektiert werden.
            </p>
            
            <h2>Wie arbeitet unser Bot?</h2>
            <p>
                Unser Bot ist darauf programmiert, das Web nach öffentlich zugänglichen Rezepten zu durchsuchen. Er sammelt wesentliche Informationen wie:
            </p>
            <ul class="recipe-list">
                <li>🍽️ Rezepttitel</li>
                <li>🔗 URL/Link zum Rezept</li>
                <li>📝 Zutatenliste</li>
                <li>📸 Ein Bild des Gerichts (sofern verfügbar)</li>
            </ul>
            <p>
                Wichtig zu wissen: Wir speichern keine vollständigen Rezepte, insbesondere keine Zubereitungsschritte. 
                Stattdessen bieten wir eine Zusammenfassung an und verlinken direkt zur Originalquelle, sodass du die vollständige Rezeptanleitung direkt auf der jeweiligen 
                Webseite einsehen kannst.
            </p>

            <h2>Warum nutzen wir einen Bot?</h2>
            <p>
                Das Ziel unseres Bots ist es, eine zentrale Anlaufstelle für alle zu schaffen, die nach Rezepten suchen - insbesondere gefiltert nach ausgewählten Zutaten, 
                ohne durch unzählige Webseiten navigieren zu müssen. Wir verstehen uns als eine Suchmaschinen-Datenbank, der verschiedene Rezepte aus dem Web zusammenfasst und 
                sie übersichtlich und einfach zugänglich macht. Unser Fokus liegt darauf, dir die Vielfalt der Rezepte zu präsentieren, damit du neue Gerichte entdecken kannst, 
                ohne die ursprünglichen Webseiten zu verdrängen.
            </p>

            <h2>Wie kann ich den Bot blockieren?</h2>
            <p>Wenn du nicht möchtest, dass unser Bot deine Webseite crawlt, kannst du ihn einfach in deiner <strong>robots.txt</strong>-Datei blockieren. Füge dazu die folgende Zeile in deine <code>robots.txt</code> ein:</p>

            <div class="info-box">
                <code>
                User-agent: FindMyRecipeBot/1.0 (+https://finde-mein-rezept.de/botinfo) <br>
                Disallow: /
                </code>
            </div>

            <p>Wenn der Bot über die <code>robots.txt</code>-Datei blockiert wird, wird er keinen weiteren Zugriff auf deine Webseite versuchen. Es werden keine Daten mehr von deiner Webseite erfasst oder gespeichert.</p>

            <h2>Respekt gegenüber Urhebern und Webseitenbetreibern</h2>
            <p>
                Wir achten streng darauf, die Rechte von Webseitenbetreibern und Autoren zu respektieren. Daher verlinken wir stets direkt zur Originalquelle, 
                ohne den gesamten Inhalt der Seite auf unserer Plattform anzuzeigen. Uns ist bewusst, wie wichtig es für viele Seiten ist, den Traffic und die Interaktionen auf 
                ihren eigenen Plattformen zu behalten. Deshalb sehen wir uns als Ergänzung, die dabei hilft, neue Besucher auf diese Seiten zu bringen, und nicht als Konkurrenz.
            </p>
            <p>
                Falls du der Betreiber einer Rezeptseite bist und Fragen hast oder nicht möchtest, dass wir deine Rezepte auf Finde-Mein-Rezept.de anzeigen, lass es uns bitte wissen. 
                Wir nehmen solche Anfragen sehr ernst und werden deinen Content auf Wunsch umgehend entfernen.<br/>
            </p>

            <h2>Wie kannst du als Webseitenbetreiber von uns profitieren?</h2>
            <p>
                Indem wir deinen Rezepten eine größere Reichweite geben, können neue Nutzer auf deine Webseite aufmerksam werden. 
                Wir präsentieren nur eine Übersicht des Rezepts, was bedeutet, dass interessierte Besucher auf den Link klicken müssen, um die vollständige Anleitung und weitere 
                Details auf deiner Seite zu sehen. Dies kann zu mehr Besuchern und einer gesteigerten Sichtbarkeit deiner Inhalte führen.
            </p>

            <h2>Technische Details und API-Anbindung</h2>
            <p>
                Wir sind immer offen für Kooperationen und freuen uns über die Möglichkeit, mit Anbietern zusammenzuarbeiten, die eine API oder andere technische Schnittstellen zur 
                Verfügung stellen, um den Zugriff auf ihre Rezepte effizienter zu gestalten. Falls du über eine solche Lösung verfügst, kontaktiere uns gerne. 
                Wir sind bereit, unsere Integration so zu gestalten, dass sie deinen Anforderungen entspricht.
            </p>

            <h2>Kontakt und weitere Informadtionen</h2>
            <p>
                Falls du weitere Fragen hast, uns Feedback geben möchtest oder spezielle Wünsche hast, zögere bitte nicht, uns zu kontaktieren. 
                Wir sind immer daran interessiert, unseren Service zu verbessern und die bestmögliche Nutzererfahrung sowohl für Rezeptsuchende als auch für Webseitenbetreiber zu gewährleisten.
                <br/>E-Mailadresse: <a href="mailto:kontakt@finde-dein-rezept.de">kontakt@finde-dein-rezept.de</a>
            </p>
            <footer>
                &copy; 2024 Finde-Mein-Rezept.de
            </footer>
        </div>
    </div>