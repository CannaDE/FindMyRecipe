<?php
namespace fmr\system\http\request;

use fmr\Singleton;

class ControllerHandler extends Singleton {

    /**
     * array of <ControllerName> to <controller-name> mappings
     * @var array
     */
    protected array $lookCache = [];

    /**
     * the regex pattern for controller and id
     * example.com/{controller}/{id} -> {id} optional
     * @var string
     */
    public string $pattern = "";


    /**
     * @var string
     */
    protected $controllers;

    public string $defaultController = "login";

    public array $routeData = [];

    /**
     * page controller list
     * @var PageList|DatabaseObject[]
     */
    protected PageList|array $pageControllersList;

    protected function init() {
        parent::init();
        $this->controllers = RouteHandler::getInstance()->getCaseInsensitiveControllers();

        $this->pattern = "~/?
			(?:
				(?P<controller>
					(?:
						[a-z][a-z0-9]+
						(?:-[a-z][a-z0-9]+)*
					)+
				)
				(?:/|$)
				(?:
					(?P<id>\d+)
				)?
			)?
		~x";

    }

    public static function getPageByClassname(string $controller) {
        $sql = "SELECT *
                FROM cfw_pages page_table
                WHERE page_table.pageController LIKE %?%";
        $statement = TimeMonitoring::getDB()->prepare($sql);
        $statement->execute([$controller]);
        return $statement->fetchSingleRow();
    }

    public static function getDefaultControllerPage() {
        $sql = "SELECT *
                FROM cfw_pages page_table
                WHERE isDefault = ?";
        $statement = TimeMonitoring::getDB()->prepare($sql);
        $statement->execute([1]);
        return $statement->fetchSingleRow();
    }

    /**
     * @method resolve();
     * @param string $controller
     * @param bool $isApRequest
     * @return string[]
     * @throws IllegalLinkException
     * @throws SystemException
     */
    public function resolve(string $controller, bool $isApRequest = false): array
    {
        if (!preg_match('/^[a-z][a-z0-9]+(?:-[a-z][a-z0-9]+)*$/', $controller, $matches)) {
            throw new RequestHandlerException(sprintf("Malformed controller name '%s'", $controller));
        }

        $classData = $this->getLegacyClassData($controller);

        if($classData === null) {
            $part = explode('-', $controller);
            $part = array_map('ucfirst', $part);
            $controller = implode('', $part);


            $classData = $this->getClassData($controller, false, 'page');

            if(!$classData) {
                $classData = $this->getClassData($controller, false, 'form');

            }
            if(!$classData) {
                $classData = $this->getClassData($controller, false, 'action');
               // cfwDebug(class_exists("cfw\action\LogoutAction"), $classData);
            }
        }

        if($classData === null) {
            throw new IllegalLinkException();
        }
        $this->routeData = $classData;
        return $classData;
    }

    /**
     * Lookups the list of legacy controller names that violate the name
     * schema, e.g. are named 'BBCodeList' instead of `BbCodeList`.
     *
     * @return      string[]|null   className and controller, or null if this is not a legacy controller name
     */
    public function getLegacyClassData(string $controller, bool $isApRequest = false): ?array {
            if (\preg_match('/\\\\(?P<controller>[^\\\\]+)(Action|Form|Page)$/', $controller, $matches)) {

                return [
                    'controller' => $matches['controller']
                ];
            }
        return null;
    }

    /**
     * @param string $controller
     * @param bool $isApRequest
     * @param string $pageType
     * @return array|null
     */
    protected function getClassData(string $controller, bool $isApRequest, string $pageType ): ?array
    {
        $className = 'cfw\\' . $pageType . '\\' . $controller . \ucfirst($pageType);


        if($pageType === 'action') {

        }


        if(!class_exists($className))
            return null;
        $reflection = new \ReflectionClass($className);
        if(!$reflection->isInstantiable())
            return null;



        return [
            'className' => $className,
            'controller' => StringUtil::firstCharToLowerCase($controller),
            'pageType' => $pageType
        ];
    }


    /**
     * Transforms a controller (e.g. BoardList) into its URL representation (e.g. board-list).
     */
    public static function transformController(string $controller): string
    {
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

    /**
     * Transforms given controller into its url representation.
     *
     * Controller -> URL
     *
     * @param string $controller controller class, e.g. 'MembersList'
     * @return  string      url representation of controller, e.g. 'members-list'
     */
    public function lookup(string $controller): string {

        $lookupKey = "cfw" . '-' . $controller;

        if (isset($this->lookCache[$lookupKey])) {
            return $this->lookCache[$lookupKey];
        }
            $urlController = self::transformController($controller);
        $this->lookCache[$lookupKey] = $urlController;
        return $urlController;
    }

    /**
     * @inheritDoc
     */
    public function match($requestURL): bool {

        if (preg_match($this->pattern, $requestURL, $matches)) {

            $this->routeData['controller'] = (!isset($matches['controller'])) ? ControllerHandler::getInstance()->defaultController : $matches['controller'];
            $this->routeData['isDefaultController'] = (!isset($matches['controller']));
            return true;
        }
        return false;
    }

    public function getRouteData(): array {
        return $this->routeData;
    }
}