<?php

namespace JonasOF\CpanelDnsUpdater;

use JonasOF\CpanelDnsUpdater\IPGetter;
use Exception;
use Symfony\Component\Translation\Translator;
use JonasOF\CpanelDnsUpdater\Config;
use Monolog\Logger;

class HttpIPGetter implements IPGetter
{
    public function __construct(Config $config, Translator $messages, Logger $logger)
    {
        $this->config = $config;
        $this->messages = $messages;
        $this->logger = $logger;
    }

    public function getMyRemoteIp(string $ip_type): string
    {
        $getter = $this->config->get('modes')[$ip_type . "_getter"];

        $remote_ip = trim(file_get_contents($getter));

        if (($remote_ip === false) || (!filter_var($remote_ip, FILTER_VALIDATE_IP))) {
            $this->logger->error($this->messages->trans("RETRIVE_ERROR_MESSAGE"));

            throw new Exception($this->messages->trans("RETRIVE_ERROR_MESSAGE"));
        }

        return $remote_ip;
    }
}
