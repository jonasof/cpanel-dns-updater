<?php

namespace JonasOF\CpanelDnsUpdater\UpdateResult;

class DNSChanged implements ResultInterface
{
    public function getMessageKey(): string
    {
        return "DNS_IP_UPDATED_MESSAGE";
    }

    public function isSuccessful(): bool
    {
        return true;
    }
}
