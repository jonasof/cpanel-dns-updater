<?php

Namespace JonasOF\CpanelDnsUpdater;

use Exception;
use JonasOF\CpanelDnsUpdater\Updater;

/**
 * @copyright JonasOF 2014, 2015, 2018, 2019 (MIT License)
 */
class UpdaterAllTypes
{
    private $config;
    private $updater;

    const API_VERSION = 2;

    function __construct($config, Updater $updater)
    {
        $this->config = $config;
        $this->updater = $updater;
    }

    public function update_domains()
    {
        foreach (['ipv4', 'ipv6'] as $ip_type) {
            if (!$this->config->modes[$ip_type]) {
                continue;
            }

            try {
                $this->updater->update_domains($ip_type);
            } catch (Exception $e) {
                continue;
            }
        }
    }
}