<?php

namespace JonasOF\CpanelDnsUpdater;

class Logger
{
    static function log($INFO)
    {
        file_put_contents(_LOG_DIR . "/log", date("Y-m-d H:i") . " - " . $INFO . "\n", FILE_APPEND);
        if (_VERBOSE)
            echo $INFO;
    }
}
