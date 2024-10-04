<?php
namespace fmr;

use fmr\system\exception\ErrorException;
use fmr\system\exception\SystemException;
use fmr\system\http\request\RequestHandler;
use fmr\system\database\Database;

use fmr\system\template\TemplateEngine;
use fmr\system\http\request\RouteHandler;
class FindMyRecipe {

    protected static Database|string $databaseObject = '';

    protected static TemplateEngine $templateEngine;
    public function __construct() {
        $this->initTemplateEngine();
        $this->initDatabase();
    }

    public function initDatabase(): void {
        self::$databaseObject = new Database();
    }

    protected function initTemplateEngine(): void {
        self::$templateEngine = TemplateEngine::getInstance();
        $this->assignDefaultTemplateVariables();
    }

    protected function assignDefaultTemplateVariables(): void {
        $core = $this;
        self::getTpl()->registerPrefilter(['event', 'csrfToken']);
        self::getTpl()->assignVar(['__core' => $core]);
    }



     /**
     * @param \Throwable $e
     */
    #[NoReturn]
    public static function handleExceptions(\Throwable $e): void {

        if (ob_get_level()) {
            while (ob_get_level()) {
                ob_end_clean();
            }
        }

        @\header('HTTP/1.1 500 Internal Server Error');
        try {

            if($e instanceof \TypeError) {
                preg_match('/([^"]*):([^"]*),([^"]*),([^"]*)cfw([^"]*)/', $e->getMessage(), $matches);
                if($matches) {

                    $description = $matches[2] . ", has<strong class='exception-color-red'>" . $matches[3] . "</strong> in " . $matches[5];

                    throw new SystemException($description);
                }
                throw new SystemException($e->getMessage(), $e->getCode(), $e);
            }
            //new ExceptionHandler($e);
            \fmr\system\exception\throwableShow($e);
        } catch (\Throwable $e2) {
            echo '
            <html lang="de">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Exception Handling Error</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f7f7f7;
                        color: #333;
                        padding: 20px;
                    }
                    .error-container {
                        background-color: #fff;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                        padding: 20px;
                        max-width: 800px;
                        margin: 50px auto;
                        border-left: 5px solid #ff6b6b;
                    }
                    h1 {
                        color: #ff6b6b;
                        font-size: 24px;
                        margin-bottom: 10px;
                    }
                    .message {
                        color: #333;
                        font-size: 16px;
                        margin-bottom: 20px;
                    }
                    .exception {
                        background-color: #f1f1f1;
                        padding: 10px;
                        border-radius: 5px;
                        white-space: pre-wrap;
                        font-family: "Courier New", Courier, monospace;
                        color: #d9534f;
                        overflow-x: auto;
                        word-wrap: break-word; 
                        max-height: 300px; 
                    }
                    .trace {
                        color: #888;
                        font-size: 14px;
                        margin-top: 20px;
                    }
                    footer {
                        margin-top: 20px;
                        font-size: 12px;
                        color: #777;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <h1>Ein Fehler ist aufgetreten!</h1>
                    <div class="message">
                        Eine Ausnahme trat während der Behandlung einer anderen Ausnahme auf:</div>
                    <div class="exception"><strong>Exception 1:</strong><br>' . \preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e2) . ' </div>
                    <div class="message" style="margin-top: 20px;">
                        Diese Ausnahme wurde während der Verarbeitung folgender Ausnahme ausgelöst:
                    </div>
                    <div class="exception"><strong>Exception 2:</strong><br>' . \preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e) . '</div>
                    <div class="trace">
                        Bitte kontaktieren Sie den <a href="mailto:development@finde-mein-rezept.de">Entwickler</a>, um den Fehler zu beheben.
                    </div>
                </div>
                <footer>
                    &copy; ' . date("Y") . ' Finde-Mein-Rezept.de
                </footer>
            </body>
            </html>';
            exit();
        }
    }



    public static function handle($severity, $errstr, $errfile, $errline): void {
        if (!(error_reporting() & $severity)) {
            return;
        }
       throw new ErrorException($errstr, $severity, $errfile, $errline);
    }

    final public static function getDB(): Database {
        return self::$databaseObject;
    }

    final public static function getTpl(): TemplateEngine {
        return self::$templateEngine;
    }

    public static function getPath(): string {
        return RouteHandler::getProtocol().URL;
    }

    final public static function getActiveRequest()  {
        return (RequestHandler::getInstance()->getActiveRequest() !== null) ? RequestHandler::getInstance()->getActiveRequest() : null;
    }

    public static function isLandingPage(): bool {

        if(self::getActiveRequest() === null)
            return false;

        return self::getActiveRequest()->isLandingPage();
    }

    public static function getActivePage() {
        if(self::getActiveRequest() === null)
            return false;

        return self::getActiveRequest()->getRequestObject();
    }

    /**
     * wrapper for the magic getter methods of this class
     *
     * @param string $name
     * @return mixed    value
     * @throws SystemException
     */
    public function __get(string $name) {
        $method = 'get' . StringUtil::firstCharToUpperCase($name);
        if(method_exists($this, $method))
            return $this->{$method}();

        throw new SystemException("method '" . $method . "' does not exist in class Core");
    }

    /**
     * Returns dynamically loaded core objects.
     *
     * @param string $name
     * @param array $arguments
     * @return  object
     * @throws  SystemException
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $className = \preg_replace('~^get~', '', $name);

        if (isset(self::$coreObject[$className])) {
            return self::$coreObject[$className];
        }

        $objectName = self::getCoreObject($className);

        if (!is_object($objectName) && $objectName !== null
            && class_exists($objectName)) {
            if (!is_subclass_of($objectName, Singleton::class)) {
                throw new ParentClassException($objectName, Singleton::class);
            }

            self::$coreObject[$className] = call_user_func([$objectName, 'getInstance']);


            return self::$coreObject[$className];
        }
        return null;
    }

        /**
     * @inheritDoc
     */
    final public function __call($name, array $arguments)
    {
        // bug fix to avoid php crash, see http://bugs.php.net/bug.php?id=55020
        if (!\method_exists($this, $name)) {
            return self::__callStatic($name, $arguments);
        }

        throw new \BadMethodCallException("Call to undefined method TimeMonitoring::{$name}().");
    }
}