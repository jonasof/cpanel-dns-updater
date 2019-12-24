<?php

namespace JonasOF\CpanelDnsUpdater\Models;

use JonasOF\CpanelDnsUpdater\Models\IP;

class SubdomainChange
{
    public $subdomain;
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
}
