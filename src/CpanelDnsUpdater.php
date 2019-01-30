<?php

Namespace JonasOF\CpanelDnsUpdater;

use Gufy\CpanelPhp\Cpanel;
use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\File;
use Exception;

/**
 * @copyright JonasOF 2014, 2015, 2018 (MIT License)
 */
class CpanelDnsUpdater
{
    /** @var Cpanel $cpanel */
    private $cpanel;
    private $config;
    private $messages;
    private $cache;

    function __construct($config = false, $messages = false)
    {
        $this->config = $config;
        $this->messages = $messages;

        $this->cpanel = new Cpanel([
            "host" => $this->config->url,
            "username" => $this->config->user,
            "password" => $this->config->password,
            "auth_type" => "password",
        ]);
        $this->cpanel->setConnectionTimeout($this->config->connection_timeout);

        $adapter = new File(_CACHE_DIR);
        $adapter->setOption('ttl', $this->config->cache_ttl);
        $this->cache = new Cache($adapter);
    }

    public function update_domains()
    {
        foreach (['ipv4', 'ipv6'] as $ip_type) {
            $zoneType = $ip_type === 'ipv6' ? 'AAAA' : 'A';

            if (! $this->config->modes[$ip_type])
                continue;

            try {
                $real_ip = $this->get_my_remote_ip($this->config->modes[$ip_type . "_getter"]);
            } catch(Exception $e) {
                continue;
            }

            if ($this->config->use_ip_cache &&
                $real_ip == $this->cache->get($ip_type)) {
                CpanelDnsUpdaterLogger::log($this->messages["CACHE_EQUAL_REMOTE_MESSAGE"]);
                continue;
            }

            foreach ($this->config->subdomains_to_update as $subdomain) {
                $table_bruta = $this->get_table_of_json();

                $subdomain .= ".";

                $json_table = $this->get_table_filtrada($table_bruta);
                $serial_number = (int) $this->get_serial_number($table_bruta);

                $info_dominio = $this->find_zone_by_domain($subdomain, $json_table, $zoneType);

                if (is_null($info_dominio)) {
                    CpanelDnsUpdaterLogger::log($this->messages["ZONE_NOT_FIND"] . ": $subdomain $zoneType");
                    continue;
                }

                if ($info_dominio->address != $real_ip) {
                    $this->change_dns_ip($subdomain, $real_ip, $info_dominio->line, $serial_number, $zoneType);
                    $serial_number++;
                } else {
                    CpanelDnsUpdaterLogger::log($this->messages["REAL_EQUAL_DOMAIN_MESSAGE"]);
                }
            }

            if ($this->config->use_ip_cache) {
                $this->cache->set($ip_type, $real_ip);
            }
        }
    }

    private function get_my_remote_ip($getter)
    {
        $remote_ip = trim(file_get_contents($getter));
        if (($remote_ip === false) || (!filter_var($remote_ip, FILTER_VALIDATE_IP))) {
            CpanelDnsUpdaterLogger::log($this->messages["RETRIVE_ERROR_MESSAGE"]);
            throw new Exception();
        } else {
            return $remote_ip;
        }
    }

    /**
     * @return string @see /docs/sampleCpanelZonesResponse.json
     */
    private function get_table_of_json()
    {
        $response = $this->cpanel->execute_action(2, "ZoneEdit", "fetchzone", $this->config->user,
                array('domain' => $this->config->domain));

        if (($response !== false) && !strpos($response, 'could not')) {
            $resp = json_decode($response);
            if (!is_null($resp))
                return $resp;
        }

        throw new Exception($this->messages['CANNOT_CONNECT_CPANEL']);
    }

    private function get_table_filtrada($table_json)
    {
        return $table_json->cpanelresult->data[0]->record;
    }

    private function find_zone_by_domain($domain, $zone_records, $zoneType = "A")
    {
        foreach ($zone_records as $record) {
            $current_domain = isset($record->name) ? $record->name : null;
            $current_type = isset($record->type) ? $record->type : null;

            if ($current_domain === $domain && $current_type === $zoneType) {
                return $record;
            }
        }
    }

    private function get_serial_number($table_bruta)
    {
        return $table_bruta->cpanelresult->data[0]->serialnum;
    }

    private function change_dns_ip($subdomain, $real_ip, $line, $serial_number, $zoneType = "A")
    {
        $response = $this->cpanel->execute_action(2, 'ZoneEdit', 'edit_zone_record', $this->config->user,
            array(
            'name' => $subdomain,
            'class' => "IN",
            'line' => $line,
            'ttl' => "14400",
            'type' => $zoneType,
            'domain' => $this->config->domain,
            'address' => $real_ip,
            "serialnum" => (string) $serial_number));

        if (($response !== false) && !strpos($response, 'could not')) {
            CpanelDnsUpdaterLogger::log($this->messages["DNS_IP_UPDATED_MESSAGE"] . $real_ip);
        } else {
            CpanelDnsUpdaterLogger::log($this->messages["UNKNOW_UPDATE_ERROR_MESSAGE"]);
        }
    }
}