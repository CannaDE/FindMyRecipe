<?php
namespace fmr\system\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandlerMiddleware implements RequestHandlerInterface {

    private RequestHandlerInterface $handler;

    private MiddlewareInterface $middleware;

    /**
     * construct for RequestHandlerMiddleware
     *
     * @param MiddlewareInterface $middleware
     * @param RequestHandlerInterface $handler
     */
    public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $handler) {
        $this->middleware = $middleware;
        $this->handler = $handler;
    }

    /**
     * @inheritdoc
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        return $this->middleware->process($request, $this->handler);
    }
}