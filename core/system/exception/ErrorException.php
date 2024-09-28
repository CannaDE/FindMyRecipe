<?php
namespace fmr\system\exception;

class ErrorException extends LoggedException {
    protected int $severity;

    public function __construct($message, $severity, $errorFile, $errorLine) {
        parent::__construct($message, 0, $this);
        $this->message = $message;
        $this->severity = $severity;
        $this->file = $errorFile;
        $this->line = $errorLine;
    }

    public function getSeverity(): int {
        return $this->severity;
    }
}