<?php
namespace fmr\system\exception\template;

use fmr\system\exception\ErrorException;
use fmr\system\exception\SystemException;
use fmr\system\template\TemplateEngine;

class TemplateCompilerException extends TemplateEngineException {

    public function __construct(string $message = "",string $description = "", $file = '', $line = 0, array $informations = [],  int $code = 0) {
        parent::__construct($message, $code);
        $this->code = E_COMPILE_ERROR;
        $this->informations = $informations;
        if(!empty($file)) {
            $this->file = $file;
        }

    }
}