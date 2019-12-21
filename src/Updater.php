<?php

Namespace JonasOF\CpanelDnsUpdater;

use Desarrolla2\Cache\Cache;

use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Exceptions\ZoneNotFound;
use JonasOF\CpanelDnsUpdater\Logger;

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

    function __construct($config, $messages, CpanelApi $cpanel, Cache $cache, IPGetter $ip_getter, Logger $logger)
    {
        $this->config = $config;
        $this->messages = $messages;
        $this->cpanel = $cpanel;
        $this->cache = $cache;
        $this->ip_getter = $ip_getter;
        $this->logger = $logger;
    }

    public function update_domains($ip_type)
    {
        $zoneType = $ip_type === 'ipv6' ? 'AAAA' : 'A';

        $this->execute_with_cached_ip($ip_type, function ($real_ip) use ($zoneType) {
            foreach ($this->config->subdomains_to_update as $subdomain) {
                try {
                    $this->update_domain(new Domain([
                        "subdomain" => $subdomain . ".",
                        "real_ip" => $real_ip,
                        "zoneType" => $zoneType,
                    ]));
                } catch(ZoneNotFound $e) {
                    continue;
                }
            }
        });
    }

    private function execute_with_cached_ip($ip_type, callable $callback) {
        $real_ip = $this->ip_getter->get_my_remote_ip($ip_type);

        if (!$this->config->use_ip_cache) {
            $callback($real_ip);
            return;
        }

        if ($real_ip == $this->cache->get($this->ip_type)) {
            $this->logger->log($this->messages->trans("REAL_EQUAL_DOMAIN_MESSAGE"));

            return;
        }

        $callback($real_ip);

        $this->cache->set($ip_type, $real_ip);
    }

    private function update_domain(Domain $subdomain)
    {
        $domain_info = $this->cpanel->get_domain_info($subdomain);

        if ($domain_info->address === $subdomain->real_ip) {
            $this->logger->log($this->messages->trans("REAL_EQUAL_DOMAIN_MESSAGE"));
            return;
        }

        $response = $this->cpanel->change_dns_ip($subdomain, $domain_info->line, $domain_info->serial_number);

        if (($response !== false) && !strpos($response, 'could not')) {
            $this->logger->log($this->messages->trans("REAL_EQUAL_DOMAIN_MESSAGE") . $subdomain->real_ip);
        } else {
            $this->logger->log($this->messages->trans("REAL_EQUAL_DOMAIN_MESSAGE"));
        }
    }
}