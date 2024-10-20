<?php

namespace fmr\system\http\request;

use fmr\Singleton;

use fmr\system\exception\RouteHandlerException;
use fmr\system\http\middleware\Middleware;
use fmr\system\http\middleware\CheckSystemEnvironment;
use fmr\system\http\middleware\CheckMaintenanceMode;
use fmr\system\http\middleware\SessionMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;
use Psr\Http\Message\RequestInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandler extends Singleton {
    protected ?Request $activeRequest = null;

    protected array $controllerData = [];

    protected function init() {}

    public function handle(): void {
        try {
            if(!RouteHandler::getInstance()->matches()) {
                if(ENABLE_DEBUG_MODE) throw new RouteHandlerException("Cannot handle request, no route matched");
                else throw new IllegalLinkException();
            }
            
            $request = ServerRequestFactory::fromGlobals(
                null,
                null,
                null,
                null,
                null,
                FilterUsingXForwardedHeaders::trustProxies(
                    ['*'],
                    [FilterUsingXForwardedHeaders::HEADER_PROTO]
            ));
            
            $buildRequest = $this->buildRequest($request);

            if($buildRequest instanceof Request) {
                $this->activeRequest = $buildRequest;

                $pipeline = new Middleware([
                    new CheckSystemEnvironment(),
                    new CheckMaintenanceMode()
                ]);

                $response = $pipeline->process($request, $this->getActiveRequest());

                if($response instanceof PlaceholderResponse) {
                    return;
                }
            }
            else $response = $buildRequest;

            $emitter = new SapiEmitter();
            $emitter->emit($response);

        } catch(CustomNamedErrorException $e) {
            throwableShow($e);
        }
    }
    
    protected function buildRequest(RequestInterface $request): Request|ResponseInterface {

        try {

            $data = RouteHandler::getInstance()->getRouteData();

            if(RouteHandler::getInstance()->isDefaultController()) {
                $routeData['controller'] = 'start';
            }

            foreach ($data as $key => $value) {
                $routeData[$key] = $value;
            }

            $domainName = DOMAIN_NAME;
            if($domainName !== $request->getUri()->getHost()) {
                $targetUrl = $request->getUri()->withHost($domainName);

                return new Response($targetUrl, 301);
            }
            if(isset($routeData['className'])) {
                $className = $routeData['className'];

            } else {
                $controller = $routeData['controller'];
                $classData = ControllerMap::getInstance()->resolve($controller);

                if(is_string($classData)) {
                    $routeData['controller'] = $classData;

                    foreach ($_GET as $key => $value) {
                        if (!empty($value) && $key != 'controller') {
                            $routeData[$key] = $value;
                        }
                    }

                    return new RedirectResponse(
                        LinkHandler::getInstance()->getLink($routeData['controller'], $routeData),
                        301
                    );
                } else {
                    $className = $classData['className'];
                }

            }

            return new Request($className);
        }
        catch (SystemException $e) {
            if (ENABLE_DEV_MODE) TimeMonitoring::handleExceptions($e);
            else throw new IllegalLinkException();
        }


    }

    public function getActiveRequest() : ?Request {
        return $this->activeRequest;
    }

    /**
     * Returns whether the request's 'x-requested-with' header is equal
     * to 'XMLHttpRequest'.
     */
    public static function isAjaxRequest(ServerRequestInterface $request): bool {
        return $request->getHeaderLine('x-requested-with') === 'XMLHttpRequest';
    }

}

