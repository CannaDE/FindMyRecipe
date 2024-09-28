<?php
namespace fmr\system\exception;

class SystemException extends LoggedException {
    public function __construct(string $message = "", int $code = 0, \Throwable $e = null) {
        parent::__construct($message, $code, $e);
    }
}