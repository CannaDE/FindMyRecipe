<?php

namespace fmr\system\http\request\route;

/**
 * Interface was implemented in classes
 * for another routes
 *
 */
interface RequestRouteInterface {
    /**
     * Builds a link upon route components.
     *
     * @param array $components
     * @return  string
     */
    public function buildLink(array $components): string;

    /**
     * Returns true if current route can handle the build request.
     *
     * @param array $components
     * @return  bool
     */
    public function canHandle(array $components): bool;

    /**
     * Returns parsed route data.
     *
     * @return  array
     */
    public function getRouteData(): array;


    /**
     * Returns true if given request url matches this route.
     *
     * @param string $requestURL
     * @return  bool
     */
    public function matches(string $requestURL): bool;
}