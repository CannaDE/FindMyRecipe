<?php

namespace fmr\system\exception;

use JetBrains\PhpStorm\NoReturn;

class AjaxException extends SystemException
{

    const MISSING_PARAMETERS = 400;

    const SESSION_EXPIRED = 401;

    const INSUFFICIENT_PERMISSIONS = 403;

    const ILLEGAL_LINK = 404;

    const METHOD_NOT_ALLOWED = 405;

    const BAD_PARAMETERS = 412;

    const INTERNAL_SERVER_ERROR = 500;
    const INTERNAL_ERROR = 530;

    public function __construct($message, $code = self::INTERNAL_ERROR)
    {
        parent::__construct($message, $code);
        if (ENABLE_DEV_MODE) {
            $stacktrace = $this->getTraceAsString();
        }
        $this->code = $code;
        $responseData = [
            'code' => $this->code,
            'errorMessage' => $this->message,
            'publicMessage' => ""
        ];

        $statusHeader = "";
        switch ($this->getCode()) {
            case self::MISSING_PARAMETERS:
                $statusHeader = '400';

                $responseData['publicMessage'] = "Die Anfrage war unvollständig und konnte nicht verarbeitet werden.";
                break;

            case self::SESSION_EXPIRED:
                $statusHeader = '409';
                break;

            case self::INSUFFICIENT_PERMISSIONS:
                $statusHeader = '403';

                $responseData['publicMessage'] = "Der Zutritt ist dir leider verwehrt. Du besitzt nicht die notwendigen Zugriffsrechte, um diese Aktion ausführen zu können.";
                break;

            case self::BAD_PARAMETERS:
                $statusHeader = '400';

                break;

            default:
            case self::ILLEGAL_LINK:
            case self::INTERNAL_ERROR:
                $statusHeader = '503';

                $responseData['code'] = $statusHeader;
                $responseData['publicMessage'] = "Es ist ein Fehler bei der Verarbeitung aufgetreten," . PHP_EOL . " bitte versuche es später erneut.";
                break;
        }

        self::sendResponse($responseData);
    }

    #[NoReturn]
    protected function sendResponse($responseData): void
    {
        header("Content-Type: application/json");
        header("HTTP/1.1 " . $responseData['code'] . " " . $this->getMessage());
        echo json_encode($responseData);
        die();
    }

}