<?php
const EXCEPTION_PRIVACY = "public";
const FMR_VERSION = "0.0.1-dev";
if(!defined('BASE_DIR')) define('BASE_DIR', __DIR__ . '/');
if(!defined('TMP_DIR')) define('TMP_DIR', BASE_DIR . 'tmp/');
if(!defined('CORE_PATH')) define('CORE_PATH', BASE_DIR . 'core/');
if(!defined('LOG_DIR')) define('LOG_DIR', 'log/');

define('SIGNATURE_SECRET', 'A8f!kL4m@S2d$B7xN&1pQ');

define('ENABLE_DEV_MODE', true);
define('LAST_UPDATE_TIME', time());

define('TIMEZONE', 'Europe/Berlin');

define('PAGE_TITLE', 'Finde-Mein-Rezept.de');
define('DOMAIN_NAME', 'rezept.test');
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', '<p>Die Rezeptsuche wird bald verfügbar sein! Besuche uns regelmäßig, um die neuesten Updates und Funktionen zu entdecken.</p>
            <p>Wir freuen uns darauf, dir bald eine Vielzahl an Rezepten basierend auf deinen Zutaten anbieten zu können. Bleib gespannt!</p>');

define('MYSQL_HOSTNAME', 'localhost');
define('MYSQL_USERNAME', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DATABASENAME', 'rezept');

define('META_DESCRIPTION', 'Deine Rezept-Suchmaschine anhand von deinen vorhandenen Zutaten');
define('META_KEYWORDS', 'Rezepte, Zutaten, Finde-Mein-Rezept, Suche, Kochbuch, Kochen');

new \fmr\FindMyRecipe();