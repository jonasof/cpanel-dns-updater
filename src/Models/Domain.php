<?php

namespace JonasOF\CpanelDnsUpdater\Models;

class Domain
{
    public $subdomain;
    public $real_ip;
    public $zoneType;

    public function __construct($params = [])
    {
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }
}
