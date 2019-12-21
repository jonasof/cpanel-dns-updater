<?php

namespace Tests\Mocks;

use JonasOF\CpanelDnsUpdater\IPGetter;

class FakeIpGetter extends IPGetter
{
    public function get_my_remote_ip(string $ip_type): string
    {
        if ($ip_type === "4") {
            return "192.168.1.104";
        } else {
            return "2001:0db8:85a3:0000:0000:8a2e:0370:7334"
        }
        
    }
}