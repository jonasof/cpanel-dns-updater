<?php

namespace JonasOF\CpanelDnsUpdater;

use JonasOF\CpanelDnsUpdater\Config\Subdomain;
use JonasOF\CpanelDnsUpdater\Exceptions\CpanelApiException;
use JonasOF\CpanelDnsUpdater\Models\IPTypes;
use JonasOF\CpanelDnsUpdater\Updater;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

class UpdaterAllTypes
{
    private $config;
    private $updater;
    private $ip_getter;

    public function __construct(
        Config $config,
        Updater $updater,
        IPGetter $ip_getter,
        Logger $logger,
        Translator $messages
    ) {
        $this->config = $config;
        $this->updater = $updater;
        $this->ip_getter = $ip_getter;
        $this->logger = $logger;
        $this->messages = $messages;
    }

    public function updateDomains()
    {
        foreach (IPTypes::getIpTypes() as $ip_type) {
            if (!$this->config->get('modes')[$ip_type]) {
                continue;
            }

            $subdomains = $this->getSubdomainsFromType($ip_type);

            if (sizeof($subdomains) === 0) {
                $this->logger->error($this->messages->trans("NO_IP_TYPE_SUBDOMAINS"), [
                    "ip_type" => $ip_type,
                ]);
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

            $this->updater->updateDomains($new_ip, $subdomains);
        }
    }

    private function getSubdomainsFromType(string $ip_type)
    {
        $subdomains = array_filter(
            $this->config->subdomainsToUpdate(),
            function (Subdomain $subdomain) use ($ip_type) {
                return in_array($ip_type, $subdomain->types);
            }
        );

        return $subdomains;
    }
}
