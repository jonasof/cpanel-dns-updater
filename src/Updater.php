<?php

Namespace JonasOF\CpanelDnsUpdater;

use Gufy\CpanelPhp\Cpanel;
use Desarrolla2\Cache\Cache;
use Exception;

use JonasOF\CpanelDnsUpdater\Exceptions\ZoneNotFound;

/**
 * @copyright JonasOF 2014, 2015, 2018, 2019 (MIT License)
 */
class Updater
{
    /** @var Cpanel $cpanel */
    private $cpanel;
    private $config;
    private $messages;
    private $cache;
    private $ip_getter;

    const API_V2 = 2;

    function __construct($config, $messages, Cpanel $cpanel, Cache $cache, IPGetter $ip_getter)
    {
        $this->config = $config;
        $this->messages = $messages;
        $this->cpanel = $cpanel;
        $this->cache = $cache;
        $this->ip_getter = $ip_getter;
    }

    public function update_domains()
    {
        foreach (['ipv4', 'ipv6'] as $ip_type) {
            if (!$this->config->modes[$ip_type]) {
                continue;
            }

            try {
                $this->update_domains_of_type($ip_type);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    private function update_domains_of_type($ip_type)
    {
        $zoneType = $ip_type === 'ipv6' ? 'AAAA' : 'A';

        $real_ip = $this->ip_getter->get_my_remote_ip($ip_type);

        if (
            $this->config->use_ip_cache &&
            $real_ip == $this->cache->get($ip_type)
        ) {
            CpanelDnsUpdaterLogger::log($this->messages["CACHE_EQUAL_REMOTE_MESSAGE"]);
            return;
        }

        foreach ($this->config->subdomains_to_update as $subdomain) {
            try {
                $this->update_domain($subdomain, $real_ip, $zoneType);
            } catch(ZoneNotFound $e) {
                continue;
            }
        }

        if ($this->config->use_ip_cache) {
            $this->cache->set($ip_type, $real_ip);
        }
    }

    private function update_domain($subdomain, $real_ip, $zoneType)
    {
        $zones = $this->get_all_zones();

        $subdomain .= ".";

        $records = $zones->record;
        $serial_number = (int) $zones->serialnum;

        $info_dominio = $this->find_zone_by_domain($subdomain, $records, $zoneType);

        if (is_null($info_dominio)) {
            CpanelDnsUpdaterLogger::log($this->messages["ZONE_NOT_FIND"] . ": $subdomain $zoneType");
            throw new ZoneNotFound();
        }

        if ($info_dominio->address !== $real_ip) {
            $this->change_dns_ip($subdomain, $real_ip, $info_dominio->line, $serial_number, $zoneType);
        } else {
            CpanelDnsUpdaterLogger::log($this->messages["REAL_EQUAL_DOMAIN_MESSAGE"]);
        }
    }

    /**
     * @return string @see /docs/sampleCpanelZonesResponse.json
     */
    private function get_all_zones()
    {
        $response = $this->cpanel->execute_action(
            self::API_V2,
            "ZoneEdit",
            "fetchzone",
            $this->config->user,
            ['domain' => $this->config->domain]
        );

        if ($response === false || strpos($response, 'could not') !== false) {
            throw new Exception($this->messages['CANNOT_CONNECT_CPANEL']);
        }

        $resp = json_decode($response);
        if (is_null($resp) || !isset($resp->cpanelresult->data[0])) {
            throw new Exception($this->messages['CANNOT_CONNECT_CPANEL']);
        }

        return $resp->cpanelresult->data[0];
    }

    private function find_zone_by_domain($domain, $zone_records, $zoneType = "A")
    {
        foreach ($zone_records as $record) {
            $current_domain = $record->name ?? null;
            $current_type = $record->type ?? null;

            if ($current_domain === $domain && $current_type === $zoneType) {
                return $record;
            }
        }
    }

    private function change_dns_ip($subdomain, $real_ip, $line, $serial_number, $zoneType = "A")
    {
        $payload = [
            'name' => $subdomain,
            'class' => "IN",
            'line' => $line,
            'ttl' => "14400",
            'type' => $zoneType,
            'domain' => $this->config->domain,
            'address' => $real_ip,
            "serialnum" => (string) $serial_number
        ];

        $response = $this->cpanel->execute_action(
            self::API_V2,
            'ZoneEdit',
            'edit_zone_record',
            $this->config->user,
            $payload
        );

        if (($response !== false) && !strpos($response, 'could not')) {
            CpanelDnsUpdaterLogger::log($this->messages["DNS_IP_UPDATED_MESSAGE"] . $real_ip);
        } else {
            CpanelDnsUpdaterLogger::log($this->messages["UNKNOW_UPDATE_ERROR_MESSAGE"]);
        }
    }
}