<?php

namespace JonasOF\CpanelDnsUpdater\Models;

use JonasOF\CpanelDnsUpdater\Models\IP;

class SubdomainsChange
{
    public $domain;
    public $subdomains;
    /** @var IP $new_ip */
    public $new_ip;

    public function __construct($params = [])
    {
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getDNSType(): string
    {
        return $this->new_ip->getDNSType();
    }

    public function buildSudomainChanges(): array
    {
    }
}
