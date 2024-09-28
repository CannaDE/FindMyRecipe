<?php
namespace fmr\page;

use cfw\system\exception\PermissionDeniedException;
use cfw\system\exception\SystemException;
use cfw\system\http\request\Request;
use cfw\system\http\request\RequestHandler;
use cfw\system\http\LinkHandler;
use cfw\system\request\ServerRequest;
use cfw\system\util\HeaderUtil;
use cfw\system\util\StringUtil;
use cfw\system\util\URLUtil;
use fmr\FindMyRecipe;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractPage implements IAbstractPage {

    /**
     * value of given action parameter
     * @var string
     */
    public string $action = '';

    public bool $allowSpidersToIndexThisPage = true;

    /**
     * canonical URL of this page
     * @var string
     */
    public string $canonicalURL = '';

    /**
     * is true if canonical URL will be enforced even if POST data is represent
     * @var bool
     */
    public bool $forceCanonicalURL = false;

    /**
     * if u need a login to see the page
     * @var bool
     */
    public bool $loginRequired = true;

    /**
     * required permissions to see the page
     * @var array
     */
    public array $neededPermissions = [];

    /**
     * name of the smarty template for the page
     * @var string
     */
    public string $templateName = '';


    public bool $useTemplates = true;

    /**
     * the name of the current page site
     * @var string
     */
    public string $pageTitle = '';


    public string $cssClassName = '';

    /**
     * @var ?ResponseInterface
     */
    private ?ResponseInterface $psr7Response = null;

    /**e
     * enable smarty template usage
     * @var bool
     */
    private bool $useTemplate = true;

    /**
     * default constructer
     * @see IAbstractBasePage::__construct().
     */
    final public function __construct() {
        //to nothing
    }

    /**
     * @inheritdoc
     */
    final public function __run() {

        $this->maybeSetPsr7Response($this->readParameters());

        if ($this->hasPsr7Response())
            return $this->getPsr7Response();

        $this->maybeSetPsr7Response($this->show());

    }



    /**
     * @inheritDoc
     */
    public function readParameters() {

        if(isset($_REQUEST['action']))
            $this->action = $_REQUEST['action'];

        // Fire Event
        //EventHandler::getInstance()->fireAction($this, 'readParameters');
    }

    /**
     * @inheritDoc
     */
    public function readData() {

        // Fire Event
        //EventHandler::getInstance()->fireAction($this, 'readData');
    }

    public function assignVariables() {

        // Fire Event
        //EventHandler::getInstance()->fireAction($this, 'assignVariables');

        FindMyRecipe::getTpl()->assign([
            'action' => $this->action,
            'allowSpidersToIndexThisPage' => $this->allowSpidersToIndexThisPage,
            'templateName' => $this->templateName,
            'pageTitle' => $this->getTitle(),
            'canonicalURL' => $this->canonicalURL,
            '__pageCssClassName', $this->cssClassName,
        ]);
    }

    public function checkPermissions() {

        // Fire Event
       // EventHandler::getInstance()->fireAction($this, 'checkPermissions');

        if(!empty($this->neededPermissions)) {
            $hasPermissions = false;
            foreach($this->neededPermissions as $permission) {
                if(FindMyRecipe::getSession()->getPermission($permission)) {
                    $hasPermissions = true;
                    break;
                }
            }

            if(!$hasPermissions)
                throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    #[NoReturn] public function show() {


        $this->checkPermissions();

        // check if current request URL matches the canonical URL
        if($this->canonicalURL && (empty($_POST) || $this->forceCanonicalURL)) {
            $canonicalURL = URLUtil::parse($this->canonicalURL);

            $requestURI = (!empty($_SERVER['UNENCODED_URL'])) ? $_SERVER['UNENCODED_URL'] : $_SERVER['REQUEST_URI'];

            if(!StringUtil::isUTF8($requestURI))
                $requestURI = mb_convert_encoding($requestURI, 'UTF-8', 'ISO-8859-1');

            // some webservers output lower-case encoding (e.g. %c3 instead of %C3)
            $requestURI = \preg_replace_callback('~%(?P<encoded>[a-zA-Z0-9]{2})~', static function ($matches) {
                return '%' . \strtoupper($matches['encoded']);
            }, $requestURI);

            // reduce successive forwarded slashes into a single one
            $requestURI = \preg_replace('~/{2,}~', '/', $requestURI);

            $requestURL = URLUtil::parse($requestURI);
            $redirect = false;
            if ($canonicalURL['path'] != $requestURL['path']) {
                $redirect = true;
            } elseif (isset($canonicalURL['query'])) {
                if (!isset($requestURL['query'])) {
                    $redirect = true;
                } else {
                    \parse_str($canonicalURL['query'], $cQueryString);
                    \parse_str($requestURL['query'], $rQueryString);

                    foreach ($cQueryString as $key => $value) {
                        if (!isset($rQueryString[$key]) || $rQueryString[$key] != $value) {
                            $redirect = true;
                            break;
                        }
                    }
                }
            }
        }


        $this->maybeSetPsr7Response(
            $this->readData()
        );

        // readData() calls submit() in AbstractForm. It might be desirable to be able
        // to return redirect responses after successfully submitting a form.
        if ($this->hasPsr7Response()) {
            return;
        }

        $this->assignVariables();

        // Fire Event
        //EventHandler::getInstance()->fireAction($this, 'show');

        // try to guess template name
        $classParts = explode('\\', static::class);
        if (empty($this->templateName)) {
            $className = preg_replace('~(Form|Page)$~', '', array_pop($classParts));

            // check if this an *Edit page and use the add-template instead
            if (str_ends_with($className, 'Edit')) {
                $className = substr($className, 0, -4) . 'Add';
            }

            $this->templateName = lcfirst($className);

            // assign guessed template name
            FindMyRecipe::getTPL()->assign('templateName', $this->templateName);
        }

        if ($this->useTemplate) {
            // show template
            FindMyRecipe::getTPL()->displayTemplate($this->templateName);
        }

    }

    final public function getTitle(): string {

        $classParts = explode('\\', static::class);
        if(empty($this->pageTitle)) {
            $className = preg_replace('~(Form|Page)$~', '', array_pop($classParts));
            $parts = \preg_split(
                '/([A-Z][a-z0-9]+)/',
                $className,
                -1,
                \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
            );
            foreach ($parts as $part) {
                $this->pageTitle .= $part . " ";
            }
        }
        return $this->pageTitle;
    }

    /**
     * Calls setResponse() if the parameter implements the ResponseInterface.
     *
     */
    final protected function maybeSetPsr7Response($response): void {
        if ($response instanceof ResponseInterface) {
            $this->setPsr7Response($response);
        }
    }

    /**
     * Sets the PSR-7 response to return. Processing will be aborted after
     * readParameters(), readData() or show() if the response is non-null
     * and the response will be returned to the RequestHandler.
     *
     */
    final protected function setPsr7Response(?ResponseInterface $response): void {
        $this->psr7Response = $response;
    }

    /**
     * Returns the current response as set using setResponse().
     *
     */
    final protected function getPsr7Response(): ?ResponseInterface {
        return $this->psr7Response;
    }

    /**
     * Returns whether the current response is non-null.
     *
     * @see IAbstractBasePage::getPsr7Response()
     * @see IAbstractBasePage::setPsr7Response()
     *
     */
    final protected function hasPsr7Response(): bool {
        return $this->psr7Response !== null;
    }
}