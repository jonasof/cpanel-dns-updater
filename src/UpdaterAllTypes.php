<?php

namespace JonasOF\CpanelDnsUpdater;

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

    public function __construct(Config $config, Updater $updater)
    {
        $this->config = $config;
        $this->updater = $updater;
    }

    public function updateDomains()
    {
        foreach (['ipv4', 'ipv6'] as $ip_type) {
            if (!$this->config->get('modes')[$ip_type]) {
                continue;
            }

            try {
                $this->updater->updateDomains($ip_type);
            } catch (Exception $e) {
                continue;
            }
        }
    }
}
