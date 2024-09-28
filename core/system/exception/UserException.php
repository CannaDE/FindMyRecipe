<?php
namespace fmr\system\exception;

use fmr\system\http\request\RequestHandler;
use fmr\FindMyRecipe;

abstract class UserException extends LoggedException
{
    /**
     * returns the message, should be used to sanitize the output
     * @return string
     */
    protected function _getMessage(): string
    {
        return $this->getMessage();
    }

    public function show()
    {
        $name = static::class;
        $exceptionClassName = \mb_substr($name, \mb_strrpos($name, '\\') + 1);
        $title = "";
        if ($this instanceof IllegalLinkException) {
            $title = "Seite nicht gefunden";
        } elseif ($this instanceof PermissionDeniedException) {
            $title = "Unzureichende Berechtigung";
        }
        FindMyRecipe::getTPL()->assign([
            'name' => $name,
            'pageTitle' => ($title != "") ? $title : "Bald verfügbar!",
            'message' => $this->getMessage(),
            'stacktrace' => $this->getTraceAsString(),
            'templateName' => '__userException',
            'exceptionClassName' => $exceptionClassName,
        ]);
        FindMyRecipe::getTPL()->displayTemplate('__userException');
    }
}