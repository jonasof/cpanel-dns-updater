<?php

namespace JonasOF\CpanelDnsUpdater\Exceptions;

use Exception;

class CpanelApiException extends Exception
{
    public static function buildFromResponse($message, $response)
    {
        $error = new self($message);

        $error->response = $response;

        return $error;
    }
}
