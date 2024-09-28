<?php

namespace fmr\system\http\request;

use fmr\system\exception\SystemException;
use fmr\Singleton;

/**
 * Resolves incoming requests and performs lookups for controller to url mappings.
 */
final class ControllerMap extends Singleton {

    /**
     * @var string[]
     */
    protected array $landingPages = [];

    /**
     * list of <ControllerName> to <controller-name> mappings
     * @var string[]
     */
    protected array $lookupCache = [];

    /**
     * @inheritDoc
     */
    protected function init() {
        $this->landingPages['core']['controller'] = 'start';
        $this->landingPages['core']['routePart'] = "start/";
    }

    /**
     * Resolves class data for given controller.
     *
     * URL -> Controller
     *
     * @param string $controller
     * @return  mixed       array containing className and controller or a string containing the controller name for aliased controllers
     * @throws SystemException
     */
    public function resolve(string $controller): mixed {
        // validate controller
        if (!\preg_match('/^[a-z][a-z0-9]+(?:-[a-z][a-z0-9]+)*$/', $controller)) {
            throw new SystemException("Malformed controller name '" . $controller . "'");
        }

        $classData = $this->getLegacyClassData($controller);

        if ($classData === null) {
            $parts = explode('-', $controller);
            $parts = array_map('ucfirst', $parts);
            $controller = implode('', $parts);

            $classData = $this->getClassData($controller,'page');

            if ($classData === null) {
                $classData = $this->getClassData($controller, 'form');
            }
            if ($classData === null) {
                $classData = $this->getClassData($controller, 'action');
            }
        }

        if ($classData === null)
            throw new SystemException("Unknown controller '" . $controller . "'");

        return $classData;
    }

    /**
     * Transforms given controller into its url representation.
     *
     * Controller -> URL
     *
     * @param string $controller controller class, e.g. 'MembersList'
     * @param bool|null $forceFrontend force transformation for frontend
     * @return  string      url representation of controller, e.g. 'members-list'
     */
    public function lookup(string $controller, bool $forceFrontend = null): string{

        $lookupKey = 'core-' . $controller;

        if (isset($this->lookupCache[$lookupKey])) {
            return $this->lookupCache[$lookupKey];
        }

        if (
            $forceFrontend
            && isset($this->customUrls['reverse']['core'][$controller])
        ) {
            $urlController = $this->customUrls['reverse']['core'][$controller];
        } else {
            $urlController = self::transformController($controller);
        }

        $this->lookupCache[$lookupKey] = $urlController;

        return $urlController;
    }


    /**
     * Lookups default controller for given application.
     *
     * @return  string[]   default controller
     * @throws  SystemException
     */
    public function lookupDefaultController(): array
    {
        $routePart = $this->landingPages['core']['routePart'];

        return [
            'controller' => $routePart,
        ];
    }

    /**
     * Returns true if given controller is the application's default.
     */
    public function isDefaultController(string $controller): bool {
        return $this->landingPages['core']['controller'] === $controller;
    }

    /**
     * Returns true if currently active request represents the global landing page.
     *
     * @param array $metaData
     */
    public function isLandingPage(string $className, array $metaData): bool
    {
        if ($className !== $this->landingPages['core']['className']) {
            return false;
        }

        return true;
    }

    /**
     * Lookups the list of legacy controller names that violate the name
     * schema, e.g. are named 'BBCodeList' instead of `BbCodeList`.
     *
     * @return      string[]|null   className and controller, or null if this is not a legacy controller name
     */
    protected function getLegacyClassData( string $controller): ?array {
        if (\preg_match('/\\\\(?P<controller>[^\\\\]+)(Action|Form|Page)$/', $controller, $matches)) {
            return [
                'className' => $controller,
                'controller' => $matches['controller'],
            ];
        }

        return null;
    }

    /**
     * Returns the class data for the active request or `null` if no proper class exists
     * for the given configuration.
     *
     * @param string $controller
     * @param string $pageType page type, e.g. 'form' or 'action'
     * @return  string[]|null   className and controller
     */
    protected function getClassData(string $controller, string $pageType): ?array {
        $className = "fmr\\" . $pageType . '\\' . $controller . \ucfirst($pageType);

        if (!class_exists($className)) {
            cfwDebug($className);
            return null;
        }

        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            return null;
        }

        return [
            'className' => $className,
            'controller' => $controller,
        ];
    }

    /**
     * Transforms a controller (e.g. BoardList) into its URL representation (e.g. board-list).
     */
    public static function transformController(string $controller): string {
        // work-around for broken controllers that violate the strict naming rules
        if (\preg_match('/[A-Z]{2,}/', $controller)) {
            $parts = \preg_split(
                '/([A-Z][a-z0-9]+)/',
                $controller,
                -1,
                \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
            );

            // fix for invalid pages that would cause single character fragments
            $sanitizedParts = [];
            $tmp = '';
            foreach ($parts as $part) {
                if (\strlen($part) === 1) {
                    $tmp .= $part;
                    continue;
                }

                $sanitizedParts[] = $tmp . $part;
                $tmp = '';
            }
            if ($tmp) {
                $sanitizedParts[] = $tmp;
            }
            $parts = $sanitizedParts;
        } else {
            $parts = \preg_split(
                '/([A-Z][a-z0-9]+)/',
                $controller,
                -1,
                \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
            );
        }

        return \strtolower(\implode('-', $parts));
    }
}