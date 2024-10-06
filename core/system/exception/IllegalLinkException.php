<?php
namespace fmr\system\exception;

use fmr\FindMyRecipe;

class IllegalLinkException extends CustomNamedErrorException {

    public function __construct() {
        parent::__construct("Du hast einen ungültigen Link aufgerufen.",);
    }

    public function __run() : void {
        @header("HTTP/1.1 404 Not Found");
        FindMyRecipe::getTpl()->assign(
            'title', 'Ungültiger Aufruf'
        );

    }
}