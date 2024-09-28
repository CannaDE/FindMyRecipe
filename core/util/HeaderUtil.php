<?php
namespace fmr\util;

use fmr\system\http\request\RouteHandler;
use fmr\timeMonitoring;

final class HeaderUtil {

    public static function sendHeaders() : void {
        @header('Content-Type: text/html; charset=UTF-8');
    }

    public static function sendJsonHeaders(): void
    {
        @header('Content-Type: application/json; charset=utf-8');
    }

    public static function sendNoCacheHeaders() : void {
        @header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        @header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
    }

    public static function redirect(string $url, bool $sendStatusCode = false, bool $temporaryRedirect = true) : void {
        if(!$sendStatusCode) {
            if($temporaryRedirect) {
                @header('HTTP/1.1 307 Temporary Redirect');
            } else {
                @header('HTTP/1.1 301 Moved Permanently');
            }
        }

        @header('cache-control: private');
        @header('Location: ' . $url);
    }

    /**
     * Alias to php setcookie() function.
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     */
    public static function setCookie($name,string $value = '', $expire = 0): void
    {

        header(
            'Set-Cookie: ' . \rawurlencode(COOKIE_SUFFIX . $name) . '=' . \rawurlencode((string)$value) . ($expire ? '; expires=' . \gmdate(
                    'D, d-M-Y H:i:s',
                    $expire
                ) . ' GMT; max-age=' . ($expire - TIME_NOW) : '') . '; path=/' . (COOKIE_DOMAIN !== null ? '; domain=' . COOKIE_DOMAIN : '') . (RouteHandler::getInstance()->secureConnection() ? '; secure' : '') . '; HttpOnly',
            false
        );
    }

    public static function delayedRedirect(string $url, string $message, int $delay) : void {
        timeMonitoring::getTpl()->assign([
            'url' => $url,
            'message' => $message,
            'wait' => $delay,
            'templateName' => 'redirect'
        ]);
        try {
            timeMonitoring::getTpl()->display('redirect.tpl');
        } catch (\SmartyException $e) {
            exit;
        } catch (\Exception $e) {
            exit;
        }
    }

    public static function isAjax() {
            return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}