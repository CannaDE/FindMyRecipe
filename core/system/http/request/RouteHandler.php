<?php

namespace fmr\system\http\request;

use fmr\Singleton;
use fmr\system\http\request\route\DefaultRequestRoute;
use fmr\system\http\request\route\RequestRouteInterface;

class RouteHandler extends Singleton {

    private static string $host;

    protected static string $basePath = '';


    private static ?bool $secure = null;

    protected bool $autoRoute = true;

    /**
     * HTTP protocol, either 'http://' or 'https://'
     * @var string
     */
    protected static string $protocol = '';

    /**
     * @var bool
     */
    static bool $routeMatchFound;

    /**
     * @var bool
     */
    static bool $pathMatchFound;

    /**
     * @var string
     */
    static string $actionName;


    /**
     * @var array
     */
    static array $requestParams = [];

    /**
     * @var string
     */
    static string $currentPath = '';

    /**
     * @var array|string
     */
    static $currentMethod;

    /**
     * @var string
     */
    static string $currentClassName = '';
    /**
     * @var bool
     */
    static bool $isAjaxRequest = false;

    /**
     * current absolute path
     * @var string
     */
    protected static string $path = '/';

    /**
     * current path info component
     * @var ?string
     */
    protected static ?string $pathInfo = null;

    /**
     * list of available routes
     * @var array|RequestRouteInterface
     */
    protected array|RequestRouteInterface $routes = [];

    /**
     * parsed route data
     * @var array
     */
    protected ?array $routeData = [];

    protected RequestRouteInterface $route;

    /**
     * true if the current controller the default controller
     * @var bool
     */
    private bool $isDefaultController = false;


    private bool $isRenamedController = false;


    private $defaultController = StartPage::class;

    /**
     * set the default routes
     *
     * @param string $basePath
     * @param bool   $autoRoute
     */
    protected function init(string $basePath = '', bool $autoRoute = true) {
        static::$basePath = rtrim($basePath, '/');

        $this->route = new DefaultRequestRoute();
        $this->addRoute($this->route);

        //EventHandler::getInstance()->fireAction($this, 'didinit');
    }

    /**
     * add a new route to the beginning of ALL routes
     *
     * @param RequestRouteInterface $route
     */
    public function addRoute(RequestRouteInterface $route) : void {
        array_unshift($this->routes, $route);
    }

    /**
     * Returns true if the controller was renamed and has already been transformed.
     *
     * @return      bool
     */
    public function isRenamedController(): bool {
        return $this->isRenamedController;
    }

    /**
     * Returns parsed route data
     *
     * @return  ?array
     */
    public function getRouteData(): ?array {
        return $this->routeData;
    }

    /**
     * Returns true if this is a secure connection.
     *
     * @return  bool
     */
    public static function secureConnection(): ?bool {
        if (self::$secure === null) {
            self::$secure = false;

            if (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                || $_SERVER['SERVER_PORT'] == 443
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            ) {
                self::$secure = true;
            }
        }

        return self::$secure;
    }

    /**
     * Returns HTTP protocol, either 'http://' or 'https://'.
     *
     * @return  string
     */
    public static function getProtocol()
    {
        if (empty(self::$protocol)) {
            self::$protocol = 'http' . (self::secureConnection() ? 's' : '') . '://';
        }

        return self::$protocol;
    }

    /**
     * Returns protocol and domain name.
     *
     * @return  string
     */
    public static function getHost() {
        if (empty(self::$host)) {
            self::$host = self::getProtocol() . $_SERVER['HTTP_HOST'];
        }

        return self::$host;
    }

    /**
     * Registers route data within $_GET and $_REQUEST.
     */
    protected function registerRouteData(): void {
        foreach ($this->routeData as $key => $value) {
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }
    }

    /**
     * Returns true if a route matches
     * first route that is able to consume all path components is used,
     *
     * @return  bool
     * @throws SystemException
     */
    public function match(): bool {
        if (ControllerHandler::getInstance()->match($_SERVER['REQUEST_URI'])) {
            $routeData = ControllerHandler::getInstance()->getRouteData();

            $classData = ControllerHandler::getInstance()->resolve($routeData['controller']);
            return true;
        }
        return false;
    }

    /**
     * Returns current path info array.
     *
     * @return array|null
     */
    public static function getPathInfo(): ?string {
        if (self::$pathInfo === null) {
            self::$pathInfo = '';

            if (!empty($_SERVER['QUERY_STRING'])) {
                // don't use parse_str as it replaces dots with underscores
                $components = \explode('&', $_SERVER['QUERY_STRING']);
                for ($i = 0, $length = \count($components); $i < $length; $i++) {
                    $component = $components[$i];

                    $pos = \mb_strpos($component, '=');
                    if ($pos !== false && $pos + 1 === \mb_strlen($component)) {
                        $component = \mb_substr($component, 0, -1);
                        $pos = false;
                    }

                    if ($pos === false) {
                        self::$pathInfo = \urldecode($component);
                        break;
                    }
                }
            }

            // translate legacy controller names
            if (\preg_match('~^(?P<controller>(?:[A-Z]+[a-z0-9]+)+)(?:/|$)~', self::$pathInfo, $matches)) {
                $parts = \preg_split(
                    '~([A-Z]+[a-z0-9]+)~',
                    $matches['controller'],
                    -1,
                    \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
                );
                $parts = \array_map('strtolower', $parts);

                self::$pathInfo = \implode('-', $parts) . \mb_substr(
                        self::$pathInfo,
                        \mb_strlen($matches['controller'])
                    );
            }
        }

        return self::$pathInfo;
    }

    /**
     * returns a list of all available routes
     *
     * @return array|RequestRouteInterface
     */
    public function getRoutes(): RequestRouteInterface|array {
        return $this->routes;
    }

    /**
     * @throws ClassNotFoundException
     * @throws ParentClassException
     * @throws SystemException
     * @throws \ErrorException
     */
    public function matches(): bool {
        foreach ($this->routes as $route) {

            if ($route->matches(self::getPathInfo())) {
                $this->routeData = $route->getRouteData();

                $this->isDefaultController = $this->routeData['isDefaultController'];
                unset($this->routeData['isDefaultController']);

                $hasController = isset($this->routeData['controller']) && $this->routeData['controller'] !== '';
                if (
                    ($hasController && $this->isDefaultController())
                    || (!$hasController && !$this->isDefaultController())
                ) {
                    throw new \DomainException(sprintf(
                        "Route implementation '%s' is buggy: Matched route must either be the default controller or a controller must be returned.",
                        $route::class
                    ));
                }

                if (isset($this->routeData['isRenamedController'])) {
                    $this->isRenamedController = $this->routeData['isRenamedController'];
                    unset($this->routeData['isRenamedController']);
                }

                $this->registerRouteData();

                return true;
            }
        }

        return false;
    }

    /**
     * @param $class
     *
     * @throws ClassNotFoundException
     * @throws ParentClassException
     * @throws RouteHandlerException
     * @throws \ErrorException
     */
    private function invoke($class) {

        if(class_exists($class, false)) {
            if(method_exists($class,  "readData") || method_exists($class, "readParameters")) {
                    $callable = new $class;
                    if(timeMonitoring::isAjax()) {
                        call_user_func_array([$callable, 'readParameters'], static::$requestParams);
                        $callable();
                    }

                    else {
                        if($callable instanceof IAbstractBasePage) {
                            call_user_func_array([$callable, 'readData'], static::$requestParams);

                        } else
                            throw new ParentClassException(static::$currentClassName, IAbstractBasePage::class);
                    }

                    $callable->execute();
            }
            else
                throw new RouteHandlerException("class ".$class." has not a implemented method 'readData'");


        } else
            throw new ClassNotFoundException(static::$currentClassName);
    }

    /**
     * Get all controller classes in directory and return
     * it as array[]
     *
     * @return array
     */
    public function getCaseInsensitiveControllers(): array {
        $data = [];


            foreach (['action', 'form', 'page'] as $pageType) {
                $path = CFW_DIR . '\\' . $pageType;
                if(!is_dir($path)) {
                    continue;
                }
                $dir = new \DirectoryIterator($path);
                foreach ($dir as $file) {
                    if ($file->isDir() || $file->isDot()) {
                        continue;
                    }

                    $fileName = $file->getBasename();
                    if (!\preg_match('~^I[A-Z][a-z]~', $fileName) && \preg_match('~[A-Z][a-z]{2,}~', $fileName)) {
                        $className = 'cfw\\' . $pageType . '\\' . $fileName;

                        $ciController = ControllerHandler::transformController($this->classNameToControllerName($className));

                        $data[$className] = mb_strcut($className, 0, -4);
                    }

                }

            }
            return $data;
    }

    private function classNameToControllerName(string $className): string {
        return \preg_replace('~^.*?\\\([^\\\]+)(?:Action|Form|Page)$~', '\\1', $className);
    }

    /**
     * Returns absolute domain path.
     *
     * @param array $removeComponents
     * @return  string
     */
    static function getPath(array $removeComponents = [])
    {
        if (empty(self::$path)) {
            // dirname return a single backslash on Windows if there are no parent directories
            $dir = \dirname($_SERVER['SCRIPT_NAME']);
            self::$path = ($dir === '\\') ? '/' : rtrim($dir, '/') . '/';;
        }

        if (!empty($removeComponents)) {
            $path = \explode('/', self::$path);
            foreach ($path as $index => $component) {
                if (empty($path[$index])) {
                    unset($path[$index]);
                }

                if (\in_array($component, $removeComponents)) {
                    unset($path[$index]);
                }
            }

            return FileUtil::addTrailingSlash('/' . \implode('/', $path));
        }

        return self::$path;
    }

    public function isDefaultController(): bool {
        return $this->isDefaultController;
    }

    /**
     * Builds a route based upon route components, this is nothing
     * but a reverse lookup.
     *
     * @param array $components
     * @param bool|null $isACP
     * @return  string
     * @throws SystemException
     */
    public function buildRoute(array $components, bool $isACP = null): string {

        foreach ($this->routes as $route) {
            if ($route->canHandle($components)) {
                return $route->buildLink($components);
            }
        }
        throw new RouteHandlerException("Unable to build route, no available route is satisfied.");
    }


}