<?php

namespace JonasOF\CpanelDnsUpdater\Config;

class Subdomain
{
    public $name;

    public $types;

    public function __construct($name, $types = ["ipv4", "ipv6"])
    {
        $this->name = $name;
        $this->types = $types;
    }
}
