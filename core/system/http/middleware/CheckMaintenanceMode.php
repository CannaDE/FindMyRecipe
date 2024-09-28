<?php
namespace fmr\system\http\middleware;

use fmr\system\exception\CustomNamedErrorException;
use fmr\system\http\request\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CheckMaintenanceMode implements MiddlewareInterface {

    /**
     * @inheritDoc
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if(RequestHandler::getInstance()->getActiveRequest()) {
            if(MAINTENANCE_MODE && RequestHandler::getInstance()->getActiveRequest()->isAvailableInMaintenanceMode()) {
                return $handler->handle($request);
            }
            
            if(MAINTENANCE_MODE) {
                header("HTTP/1.1 503 Service Unavailable");

                throw new CustomNamedErrorException(MAINTENANCE_MESSAGE);
            }
        }
        return $handler->handle($request);
    }
}