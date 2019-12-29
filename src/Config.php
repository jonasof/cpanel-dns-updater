<?php

namespace JonasOF\CpanelDnsUpdater;

use Exception;

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
}
