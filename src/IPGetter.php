<?php

namespace JonasOF\CpanelDnsUpdater;

use JonasOF\CpanelDnsUpdater\Models\IP;

interface IPGetter
{
    public function getMyRemoteIp(string $ip_type): IP;
}
