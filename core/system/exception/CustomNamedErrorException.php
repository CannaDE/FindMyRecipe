<?php
namespace fmr\system\exception;

use fmr\system\http\request\RequestHandler;

class CustomNamedErrorException extends UserException
{

    public function __construct(string $message = "")
    {
        parent::__construct($message, 0, $this);
        $this->message = $message;

    }

}