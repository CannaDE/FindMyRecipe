<?php
namespace fmr\system\exception\database;

use fmr\system\exception\SystemException;
use fmr\util\StringUtil;

class DatabaseQueryException extends SystemException {

    public string $description = "";

    public function __construct(string $message,string $description, int $errorCode = 0) {
        $this->description = $message;
        parent::__construct($description, $errorCode);
        
    }



}