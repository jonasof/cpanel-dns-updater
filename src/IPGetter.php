<?php

namespace JonasOF\CpanelDnsUpdater;

interface IPGetter
{
    public function get_my_remote_ip(string $ip_type): string;
}
