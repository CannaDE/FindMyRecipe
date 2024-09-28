<?php

namespace fmr\system\http\request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Request implements RequestHandlerInterface {
    private readonly string $className;

    private readonly bool $isLandingPage;

    private readonly array $metaData;


    /**
     * request object
     * @var ?object
     */
    private ?object $requestObject = null;

    public function __construct(string $className, array $metaData = [], bool $isLandingPage = false) {
        $this->className = $className;
        $this->metaData = $metaData;
        $this->isLandingPage = $isLandingPage;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        if ($this->requestObject === null) {
            $this->requestObject = new $this->className();
        }

        if ($this->requestObject instanceof RequestHandlerInterface) {
            return $this->requestObject->handle($request);
        } else {
            $response = $this->requestObject->__run();

            if ($response instanceof ResponseInterface) {
                return $response;
            } else {
                return new PlaceholderResponse();
            }
        }
    }

    /**
     * Returns true if this request represents the landing page.
     */
    public function isLandingPage(): bool {
        return $this->isLandingPage;
    }

    /**
     * Returns the page class name of this request.
     */
    public function getClassName(): string {
        return $this->className;
    }

    /**
     * Returns request meta data.
     *
     * @return  array
     * @since   3.0
     */
    public function getMetaData(): array {
        return $this->metaData;
    }

    /**
     * Returns the current request object.
     *
     * @return  object
     */
    public function getRequestObject(): object {
        return ($this->requestObject !== null) ? $this->requestObject : null;
    }

    /**
     * Returns true if the requested page is available during the offline mode.
     */
    public function isAvailableInMaintenanceMode(): bool {
        if (
            \defined($this->className . '::AVAILABLE_DURING_MAINTENANCE_MODE')
            && \constant($this->className . '::AVAILABLE_DURING_MAINTENANCE_MODE')
        ) {
            return true;
        }
        return false;
    }
}