<?php

namespace JonasOF\CpanelDnsUpdater;

use Exception;
use Gufy\CpanelPhp\Cpanel;
use JonasOF\CpanelDnsUpdater\Exceptions\ZoneNotFound;
use JonasOF\CpanelDnsUpdater\Logger;
use Symfony\Component\Translation\Translator;

class CpanelApi
{
    /** @var Cpanel $cpanel */
    private $cpanel;
    private $config;
    private $messages;
    private $logger;

    const API_VERSION = 2;

    function __construct($config, Translator $messages, Cpanel $cpanel, Logger $logger)
    {
        $this->config = $config;
        $this->messages = $messages;
        $this->cpanel = $cpanel;
        $this->logger = $logger;
    }

    public function get_domain_info(Domain $subdomain)
    {
        $zones = $this->get_all_zones();

        $domain_info = $this->find_zone_by_domain($subdomain, $zones->record);

        if (is_null($domain_info)) {
            $this->logger->log($this->messages->trans("ZONE_NOT_FOUND") . ": $subdomain $subdomain->zoneType");
            throw new ZoneNotFound();
        }

        $domain_info->serial_number = (int) $zones->serialnum;

        return $domain_info;
    }

    public function change_dns_ip(Domain $subdomain, $line, $serial_number)
    {
        $payload = [
            'name' => $subdomain->subdomain,
            'class' => "IN",
            'line' => $line,
            'ttl' => "14400",
            'type' => $subdomain->zoneType,
            'domain' => $this->config->domain,
            'address' => $subdomain->real_ip,
            "serialnum" => (string) $serial_number
        ];

        return $this->cpanel->execute_action(
            self::API_VERSION,
            'ZoneEdit',
            'edit_zone_record',
            $this->config->user,
            $payload
        );
    }

    /**
     * @Warning this function needs to be called every time before a update
     * (cannot be cached)
     *
     * @return string @see /docs/sampleCpanelZonesResponse.json
     */
    private function get_all_zones()
    {
        $response = $this->cpanel->execute_action(
            self::API_VERSION,
            "ZoneEdit",
            "fetchzone",
            $this->config->user,
            ['domain' => $this->config->domain]
        );

        if ($response === false || strpos($response, 'could not') !== false) {
            throw new Exception($this->messages->trans('CANNOT_CONNECT_CPANEL'));
        }

        $resp = json_decode($response);
        if (is_null($resp) || !isset($resp->cpanelresult->data[0])) {
            throw new Exception($this->messages->trans('CANNOT_CONNECT_CPANEL'));
        }

        return $resp->cpanelresult->data[0];
    }

    private function find_zone_by_domain(Domain $domain, $zone_records)
    {
        foreach ($zone_records as $record) {
            if ($record->name === $domain->subdomain && $record->type === $domain->zoneType) {
                return $record;
            }
        }
    }
}