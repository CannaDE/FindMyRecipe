<?php
/**
 * WebExpanded Framework TimeMonitoring 
 * Template: imprint
 * Compiled at: Sat, 28 Sep 2024 12:50:33 +0000
 * 
 * DO NOT EDIT THIS FILE
 */
$this->tplVars['tpl']['template'] = 'imprint';
?>
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
            <div class="header-text"><?=fmr\util\StringUtil::encodeHTML($this->tplVars['pageTitle']);?></div>
        </div>

        <!-- Inhaltsbereich -->
        <div class="content">
        <h1>Impressum</h1>

        <h2>Angaben gemäß § 5 TMG</h2>
        <p><strong>Morvai-Systems GbR</strong><br>
            Vertreten durch die Gesellschafter:<br>
            Vorname Nachname 1<br>
            Vorname Nachname 2<br>
            <br>
            Adresse<br>
            PLZ Ort<br>
            Deutschland
        </p>

        <h2>Kontakt</h2>
        <p>Telefon: [Telefonnummer]<br>
            E-Mail: [E-Mail-Adresse]</p>

        <h2>Umsatzsteuer-ID</h2>
        <p>Umsatzsteuer-Identifikationsnummer gemäß §27 a Umsatzsteuergesetz:<br>
            [USt-ID-Nr.]
        </p>

        <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV:</h2>
        <p>Vorname Nachname<br>
            Adresse<br>
            PLZ Ort
        </p>

        <h2>Haftungsausschluss (Disclaimer)</h2>

        <h3>Haftung für Inhalte</h3>
        <p>Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>

        <p>Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>

        <h3>Haftung für Links</h3>
        <p>Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar.</p>

        <p>Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>

        <h3>Urheberrecht</h3>
        <p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet.</p>

        <p>Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>

        <h2>Hinweis zur Online-Streitbeilegung</h2>
        <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr" target="_blank">https://ec.europa.eu/consumers/odr</a>. Wir sind jedoch nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>

        <h3>Datenschutzhinweis</h3>
        <p>Beim Besuch unserer Webseite erheben wir keine personenbezogenen Daten ohne Ihre Zustimmung. Für den Betrieb unseres Crawlers zur Analyse und Sammlung öffentlich zugänglicher Rezepte auf anderen Webseiten halten wir uns streng an die deutschen Datenschutzvorgaben und geben keine personenbezogenen Daten weiter.</p>

        <p>Weitere Informationen zur Funktionsweise und zur Identifizierung unseres Crawlers finden Sie auf unserer <a href="/botinfo">Botinfo-Seite</a>.</p>

        <footer>
            &copy; 2024 Morvai-Systems GbR
        </footer>
        </div>
    </div>