<?php
namespace fmr;

use fmr\system\http\request\RequestHandler;

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/global.php';
require_once __DIR__ . '/core/CoreHelper.php';

RequestHandler::getInstance()->handle();