<?php

namespace JonasOF\CpanelDnsUpdater;

use Desarrolla2\Cache\Cache;

use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Exceptions\ZoneNotFound;
use JonasOF\CpanelDnsUpdater\Models\IPTypes;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

/**
 * @copyright JonasOF 2014, 2015, 2018, 2019 (MIT License)
 */
class Updater
{
    private $cpanel;
    private $config;
    private $messages;
    private $cache;
    private $ip_getter;
    private $logger;

    public function __construct(
        Config $config,
        Translator $messages,
        CpanelApi $cpanel,
        Cache $cache,
        IPGetter $ip_getter,
        Logger $logger
    ) {
        $this->config = $config;
        $this->messages = $messages;
        $this->cpanel = $cpanel;
        $this->cache = $cache;
        $this->ip_getter = $ip_getter;
        $this->logger = $logger;
    }

    public function updateDomains($ip_type)
    {
        $zoneType = IPTypes::getDNSTypeFromIpType($ip_type);
        
        $this->executeWithCachedIp(
            $ip_type,
            function ($real_ip) use ($zoneType) {
                foreach ($this->config->get('subdomains_to_update') as $subdomain) {
                    try {
                        $this->updateDomain(
                            new Models\Domain(
                                [
                                "subdomain" => $subdomain . ".",
                                "real_ip" => $real_ip,
                                "zoneType" => $zoneType,
                                ]
                            )
                        );
                    } catch (ZoneNotFound $e) {
                        continue;
                    }
                }
            }
        );
    }

    private function executeWithCachedIp($ip_type, callable $callback)
    {
        $real_ip = $this->ip_getter->getMyRemoteIp($ip_type);

        if (!$this->config->get('use_ip_cache')) {
            $callback($real_ip);
            return;
        }

        if ($real_ip == $this->cache->get($ip_type)) {
            $this->logger->info($this->messages->trans("REAL_EQUAL_DOMAIN_MESSAGE"));

            return;
        }

        $callback($real_ip);

        $this->cache->set($ip_type, $real_ip);
    }

    private function updateDomain(Models\Domain $subdomain)
    {
        $domain_info = $this->cpanel->getDomainInfo($subdomain);

        if (!$this->config->get('force_rewrite') && $domain_info->address === $subdomain->real_ip) {
            $this->logger->info($this->messages->trans("REAL_EQUAL_DOMAIN_MESSAGE"));
            return;
        }

        $response = $this->cpanel->changeDnsIp($subdomain, $domain_info->line, $domain_info->serial_number);

        if (($response !== false) && !strpos($response, 'could not')) {
            $this->logger->info($this->messages->trans("DNS_IP_UPDATED_MESSAGE"), [
                "ip" => $subdomain->real_ip,
            ]);
        } else {
            $this->logger->error($this->messages->trans("UNKNOW_UPDATE_ERROR_MESSAGE"), [
                "response" => $response,
            ]);
        }
    }
}
