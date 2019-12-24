<?php

namespace JonasOF\CpanelDnsUpdater;

use Exception;
use Gufy\CpanelPhp\Cpanel;
use JonasOF\CpanelDnsUpdater\Exceptions\ZoneNotFound;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

class CpanelApi
{
    /**
     * @var Cpanel $cpanel
     */
    private $cpanel;
    private $config;
    private $messages;
    private $logger;

    const API_VERSION = 2;

    public function __construct(Config $config, Translator $messages, Cpanel $cpanel, Logger $logger)
    {
        $this->config = $config;
        $this->messages = $messages;
        $this->cpanel = $cpanel;
        $this->logger = $logger;
    }

    public function getDomainInfo(Models\Domain $subdomain): object
    {
        $zones = $this->getAllZones();

        $domain_info = $this->findZoneByDomain($subdomain, $zones->record);

        if (is_null($domain_info)) {
            $this->logger->error($this->messages->trans("ZONE_NOT_FOUND"), [
                "subdomain" => $subdomain->subdomain,
                "type" => $subdomain->zoneType,
            ]);

            throw new ZoneNotFound();
        }

        $domain_info->serial_number = (int) $zones->serialnum;

        return $domain_info;
    }

    public function changeDnsIp(Models\Domain $subdomain, $line, $serial_number)
    {
        $payload = [
            'name' => $subdomain->subdomain,
            'class' => "IN",
            'line' => $line,
            'ttl' => "14400",
            'type' => $subdomain->zoneType,
            'domain' => $this->config->get('domain'),
            'address' => $subdomain->real_ip,
            "serialnum" => (string) $serial_number
        ];

        return $this->cpanel->execute_action(
            self::API_VERSION,
            'ZoneEdit',
            'edit_zone_record',
            $this->config->get('user'),
            $payload
        );
    }

    /**
     * @Warning this function needs to be called every time before a update.
     * It cannot be cached because of volatile serial_number and line parameters
     *
     * @return string @see /docs/sampleCpanelZonesResponse.json
     */
    private function getAllZones()
    {
        $response = $this->cpanel->execute_action(
            self::API_VERSION,
            "ZoneEdit",
            "fetchzone",
            $this->config->get('user'),
            ['domain' => $this->config->get('domain')]
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

    private function findZoneByDomain(Models\Domain $domain, $zone_records)
    {
        foreach ($zone_records as $record) {
            $current_domain = $record->name ?? null;
            $current_type = $record->type ?? null;

            if ($current_domain === $domain->subdomain && $current_type === $domain->zoneType) {
                return $record;
            }
        }
    }
}
