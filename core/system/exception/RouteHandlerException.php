<?php
namespace fmr\system\exception;

class RouteHandlerException extends SystemException {
    public function __construct(string $message, array $informations = []) {
        parent::__construct($message, 0, $this);
    } 
}