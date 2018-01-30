<?php

namespace JonasOF\CpanelDnsUpdater;

class CpanelDnsUpdaterException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        CpanelDnsUpdaterLogger::log($message);
        parent::__construct($message, $code, $previous);
    }
}