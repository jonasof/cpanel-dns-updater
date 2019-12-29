<?php

namespace Tests\Mocks;

use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Models\SubdomainChange;

class FakeCpanelApi extends CpanelApi
{
    public function __construct()
    {
    }

    public function getDomainInfo(SubdomainChange $domain): ?object
    {
        return (object) [
            "name" => "domain1.site.com.",
            "type" => "A",
            "ttl" => "14400",
            "address" => "0.0.0.0",
            "line" => 4,
            "Line" => 4,
            "class" => "IN",
            "record" => "0.0.0.0"
        ];
    }

    public function changeDnsIp(SubdomainChange $subdomain, object $domain_info)
    {
        return (object) [];
    }
}
