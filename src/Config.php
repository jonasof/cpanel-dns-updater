<?php

namespace JonasOF\CpanelDnsUpdater;

class Config
{
    private $config;

    public function __construct(object $config)
    {
        $this->config = $config;
    }
    
    public function get(string $key)
    {
        return $this->config->$key;
    }
}
