<?php

namespace JonasOF\CpanelDnsUpdater;

use JonasOF\CpanelDnsUpdater\IPGetter;
use JonasOF\CpanelDnsUpdater\Config;
use JonasOF\CpanelDnsUpdater\Exceptions\CpanelApiException;
use JonasOF\CpanelDnsUpdater\Models\IP;

class HttpIPGetter implements IPGetter
{
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getMyRemoteIp(string $ip_type): IP
    {
        $getter = $this->config->get('modes')[$ip_type . "_getter"];

        $remote_ip = trim(file_get_contents($getter));

        if (filter_var($remote_ip, FILTER_VALIDATE_IP) === false) {
            throw new CpanelApiException("RETRIVE_ERROR_MESSAGE", $remote_ip);
        }

        return new IP($ip_type, $remote_ip);
    }
}
