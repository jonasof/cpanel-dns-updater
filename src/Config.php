<?php

namespace JonasOF\CpanelDnsUpdater;

use Exception;
use JonasOF\CpanelDnsUpdater\Config\Subdomain;

class Config
{
    private $config;

    private $fillable_required_fields = ["url", "user", "password", "subdomains_to_update", "domain"];

    public function __construct(array $config, array $defaults)
    {
        foreach ($this->fillable_required_fields as $requiredField) {
            if (empty($config[$requiredField])) {
                throw new Exception("Field $requiredField not setted in config");
            }
        }

        $this->config = (object) array_replace_recursive($defaults, $config);
    }
    
    public function get(string $key)
    {
        return $this->config->$key;
    }

    /** @return Subdomain[] */
    public function subdomainsToUpdate()
    {
        return array_map(function ($subdomain) {

            if (is_string($subdomain)) {
                return new Subdomain($subdomain);
            }

            if (is_array($subdomain)) {
                return new Subdomain($subdomain["name"], $subdomain["types"]);
            }

            throw new Exception("each subdomains_to_update item should be a string or an array");
        }, $this->get("subdomains_to_update"));
    }
}
