<?php

namespace JonasOF\CpanelDnsUpdater;

use Gufy\CpanelPhp\CpanelInterface;
use JonasOF\CpanelDnsUpdater\Exceptions\CpanelApiException;

class CpanelApi
{
    /**
     * @var CpanelInterface $cpanel
     */
    private $cpanel;
    private $config;

    private $serial_number;
    private $zones;

    const API_VERSION = 2;

    public function __construct(Config $config, CpanelInterface $cpanel)
    {
        $this->config = $config;
        $this->cpanel = $cpanel;
    }

    public function getDomainInfo(Models\SubdomainChange $domain): ?object
    {
        $zones = $this->getAllZonesCached();

        foreach ($zones->record as $record) {
            $current_domain = $record->name ?? null;
            $current_type = $record->type ?? null;

            if ($current_domain === $domain->subdomain && $current_type === $domain->getDNSType()) {
                return $record;
            }
        }

        return null;
    }

    private function getAllZonesCached()
    {
        if (!$this->zones) {
            $this->zones = $this->getAllZones();
        }

        return $this->zones;
    }

    /**
     * @return string @see /docs/sampleCpanelZonesResponse.json
     */
    private function getAllZones()
    {
        $response = $this->cpanel->cpanel(
            "ZoneEdit",
            "fetchzone",
            $this->config->get('user'),
            ['domain' => $this->config->get('domain')]
        );
        
        $zones = json_decode($response)->cpanelresult->data[0] ?? null;

        $isSuccessful = $zones->status ?? false;

        if (!$isSuccessful) {
            throw CpanelApiException::buildFromResponse('API_ERROR_WHILE_FETCHING_ZONES', $response);
        }

        $this->serial_number = (int) $zones->serialnum;

        return $zones;
    }

    public function changeDnsIp(Models\SubdomainChange $subdomain, object $domain_info): string
    {
        $payload = [
            'name' => $subdomain->subdomain,
            'class' => $domain_info->class,
            'line' => $domain_info->line,
            'ttl' => $domain_info->ttl,
            'type' => $subdomain->getDNSType(),
            'domain' => $this->config->get('domain'),
            'address' => $subdomain->new_ip->value,
            "serialnum" => (string) $this->serial_number
        ];

        $response = $this->cpanel->cpanel(
            'ZoneEdit',
            'edit_zone_record',
            $this->config->get('user'),
            $payload
        );

        $result = json_decode($response)->cpanelresult->data[0]->result ?? null;

        $isSuccessful = $result->status ?? false;

        if (!$isSuccessful) {
            throw CpanelApiException::buildFromResponse('API_ERROR_WHILE_UPDATING_IP', $response);
        }

        $this->serial_number = $result->newserial;

        return $response;
    }
}
