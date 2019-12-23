<?php

namespace JonasOF\CpanelDnsUpdater;

interface IPGetter
{
    public function getMyRemoteIp(string $ip_type): string;
}
