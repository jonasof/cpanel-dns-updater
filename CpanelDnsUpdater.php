<?php

use Gufy\CpanelPhp\Cpanel;

/**
 * Cpanel_Dns_Updater 
 * @copyright JonasOF 2014, 2015
 */
class CpanelDnsUpdater
{

    /** @var Cpanel $cpanel */
    private $cpanel;
    private $config;
    private $messages;

    function __construct($config = false, $messages = false)
    {
        if ($config == false) {
            global $CDUconfig;
            $config = $CDUconfig;
        }
        $this->config = $config;

        if ($messages == false) {
            global $CDU_LANGUAGES;
            $messages = $CDU_LANGUAGES["EN"];
        }
        $this->messages = $messages;

        $this->cpanel = new Cpanel([
            "host" => $this->config->url,
            "username" => $this->config->user,
            "password" => $this->config->password,
            "auth_type" => "password",
        ]);
    }

    public function update_domains()
    {
        $real_ip = $this->get_my_remote_ip();

        if ($real_ip == $this->get_my_cache_ip()) {
            CpanelDnsUpdaterLogger::log($this->messages["CACHE_EQUAL_REMOTE_MESSAGE"]);
            exit;
        }

        foreach ($this->config->subdomains_to_update as $subdomain) {

            $subdomain .= ".";

            $table_bruta = $this->get_table_of_json();
            $json_table = $this->get_table_filtrada($table_bruta);
            $serial_number = (int) $this->get_serial_number($table_bruta);

            $info_dominio = ( $this->get_registro_by_domain($subdomain, $json_table));

            if ($info_dominio->address != $real_ip) {

                $this->change_dns_ip($subdomain, $real_ip, $info_dominio->line, $serial_number);
                $serial_number ++;
            } else {
                CpanelDnsUpdaterLogger::log($this->messages["REAL_EQUAL_DOMAIN_MESSAGE"]);
            }
        }

        $this->set_cache_ip($real_ip);
    }

    private function get_my_remote_ip()
    {
        $remote_ip = trim(file_get_contents(_IP_GETTER));
        if (($remote_ip === false) || (!filter_var($remote_ip, FILTER_VALIDATE_IP))) {
            CpanelDnsUpdaterLogger::log($this->messages["RETRIVE_ERROR_MESSAGE"]);
            exit;
        } else {
            return $remote_ip;
        }
    }

    private function get_my_cache_ip()
    {
        if ($this->config->use_ip_cache && is_file(_CACHE_DIR . "/ip"))
            return file_get_contents(_CACHE_DIR . "/ip");

        return "";
    }

    private function set_cache_ip($ip)
    {
        file_put_contents(_CACHE_DIR . "/ip", $ip);
    }

    private function get_table_of_json()
    {
        $response = $this->cpanel->execute_action(2, "ZoneEdit", "fetchzone", $this->config->user, 
                array('domain' => $this->config->domain));
        
        if (($response !== false) && !strpos($response, 'could not')) {
            $resp = json_decode($response);
            if (!is_null($resp))
                return $resp;
        }

        //else
        throw new Exception($this->messages['CANNOT_CONNECT_CPANEL']);
    }

    private function get_table_filtrada($table_json)
    {
        return $table_json->cpanelresult->data[0]->record;
    }

    private function get_registro_by_domain($domain, $table_filtrada)
    {
        foreach ($table_filtrada as $key => $registro) {
            $nome_dom = @$registro->name;
            if ($nome_dom == $domain) {
                return $registro;
            }
        }
    }

    private function get_serial_number($table_bruta)
    {
        return $table_bruta->cpanelresult->data[0]->serialnum;
    }

    private function change_dns_ip($subdomain, $real_ip, $line, $serial_number)
    {
        $response = $this->cpanel->execute_action(2, 'ZoneEdit', 'edit_zone_record', $this->config->user,
            array(
            'name' => $subdomain,
            'class' => "IN",
            'line' => $line,
            'ttl' => "14400",
            'type' => 'A',
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

class CpanelDnsUpdaterException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        CpanelDnsUpdaterLogger::log($message);
        parent::__construct($message, $code, $previous);
    }

}

class CpanelDnsUpdaterLogger
{
    static function log($INFO)
    {
        file_put_contents(_LOG_DIR . "/log", date("Y-m-d H:i") . " - " . $INFO . "\n", FILE_APPEND);
        if (_VERBOSE)
            echo $INFO;
    }

}
