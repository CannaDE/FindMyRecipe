<?php
const EXCEPTION_PRIVACY = "public";
const FMR_VERSION = "0.0.1-dev";
const ENABLE_DEBUG_MODE = true;
if(!defined('BASE_DIR')) define('BASE_DIR', __DIR__ . '/');
if(!defined('TMP_DIR')) define('TMP_DIR', BASE_DIR . 'tmp/');
if(!defined('CORE_PATH')) define('CORE_PATH', BASE_DIR . 'core/');
if(!defined('LOG_DIR')) define('LOG_DIR', 'log/');

define('PAGE_TITLE', 'Finde-Mein-Rezept.de');
define('DOMAIN_NAME', 'rezept.test');
define('MAINTENANCE_MODE', true);
define('MAINTENANCE_MESSAGE', '<p>Die Rezeptsuche wird bald verfügbar sein! Besuche uns regelmäßig, um die neuesten Updates und Funktionen zu entdecken.</p>
            <p>Wir freuen uns darauf, dir bald eine Vielzahl an Rezepten basierend auf deinen Zutaten anbieten zu können. Bleib gespannt!</p>');

new \fmr\FindMyRecipe();