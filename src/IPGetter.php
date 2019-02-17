<?php

namespace JonasOF\CpanelDnsUpdater;

class IPGetter
{
    function __construct($config, $messages)
    {
        $this->config = $config;
        $this->messages = $messages;
    }

    public function get_my_remote_ip(string $ip_type): string
    {
        $getter = $this->config->modes[$ip_type . "_getter"];

        $remote_ip = trim(file_get_contents($getter));

        if (($remote_ip === false) || (!filter_var($remote_ip, FILTER_VALIDATE_IP))) {
            // CpanelDnsUpdaterLogger::log($this->messages["RETRIVE_ERROR_MESSAGE"]);
            throw new Exception($this->messages["RETRIVE_ERROR_MESSAGE"]);
        }

        return $remote_ip;
    }
}
