<?php

namespace Tests\Mocks;

use JonasOF\CpanelDnsUpdater\IPGetter;
use JonasOF\CpanelDnsUpdater\Models\IPTypes;

class FakeIPGetter implements IPGetter
{
    public function getMyRemoteIp(string $ip_type): string
    {
        if ($ip_type === IPTypes::IPV4) {
            return "90.112.128.170";
        } else {
            return "2001:0db8:85a3:0000:0000:8a2e:0370:7334";
        }
    }
}
