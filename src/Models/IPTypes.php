<?php

namespace JonasOF\CpanelDnsUpdater\Models;

use Exception;

class IPTypes
{
    const IPV4 = "ipv4";
    const IPV6 = "ipv6";

    const DNS_TYPE_MAPPER = [
        self::IPV4 => "A",
        self::IPV6 => "AAAA",
    ];

    public static function getIpTypes(): array
    {
        return [self::IPV4, self::IPV6];
    }

    public static function getDNSTypeFromIpType(string $ip_type): string
    {
        if (!in_array($ip_type, self::getIpTypes())) {
            throw new Exception("Invalid IP Type $ip_type");
        }

        return self::DNS_TYPE_MAPPER[$ip_type];
    }
}
