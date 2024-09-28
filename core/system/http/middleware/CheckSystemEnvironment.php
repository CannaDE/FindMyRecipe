<?php
namespace fmr\system\http\middleware;

use fmr\system\exception\CustomNamedErrorException;
use fmr\system\http\request\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CheckSystemEnvironment implements MiddlewareInterface {

    /**
     * @inheritDoc
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if(RequestHandler::getInstance()->getActiveRequest()) {

            if(!(80100 <= PHP_VERSION_ID && PHP_VERSION_ID <= 82000)) {
                header("HTTP/1.1 500 Internal Server Error");

                throw new CustomNamedErrorException("
                Fehlerhafte Server-Konfiguration: Die eingesetzte Version von „PHP” ist nicht kompatibel, ein Betrieb ist nicht möglich. 
                Es wird mindestens PHP in Version 8.1 benötigt.");
            }
        }
        return $handler->handle($request);
    }
}