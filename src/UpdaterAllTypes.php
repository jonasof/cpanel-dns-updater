<?php

namespace JonasOF\CpanelDnsUpdater;

use JonasOF\CpanelDnsUpdater\Exceptions\CpanelApiException;
use JonasOF\CpanelDnsUpdater\Models\IPTypes;
use JonasOF\CpanelDnsUpdater\Updater;

class UpdaterAllTypes
{
    private $config;
    private $updater;
    private $ip_getter;

    public function __construct(
        Config $config,
        Updater $updater,
        IPGetter $ip_getter
    ) {
        $this->config = $config;
        $this->updater = $updater;
        $this->ip_getter = $ip_getter;
    }

    public function updateDomains()
    {
        foreach (IPTypes::getIpTypes() as $ip_type) {
            if (!$this->config->get('modes')[$ip_type]) {
                continue;
            }

            try {
                $new_ip = $this->ip_getter->getMyRemoteIp($ip_type);
            } catch (CpanelApiException $e) {
                $this->logger->error($this->messages->trans($e->getMessage()), [
                    "ip_type" => $ip_type,
                    "response" => $e->response,
                ]);

                continue;
            }

            $this->updater->updateDomains($new_ip, $this->config->get('subdomains_to_update'));
        }
    }
}
