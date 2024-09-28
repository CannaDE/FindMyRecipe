<?php
namespace fmr\system\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface {
 
    private array $middlewares = [];

    public function __construct(array $middlewares = []) {
        $this->middlewares = $middlewares;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        foreach(array_reverse($this->middlewares) as $middleware) {
            $handler = new RequestHandlerMiddleware($middleware, $handler);
        }

        return $handler->handle($request);
    }
}
