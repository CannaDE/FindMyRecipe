<?php
namespace fmr\system\exception\template;

use fmr\system\exception\ErrorException;
use fmr\system\exception\SystemException;

class TemplateEngineException extends SystemException {

    public function __construct(string $message = "",int $code = 0) {
        parent::__construct($message, $code);
        $this->code = $code;
    }
}