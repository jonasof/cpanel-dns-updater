<?php

namespace JonasOF\CpanelDnsUpdater\UpdateResult;

class SameIpThanRemote implements ResultInterface
{
    public function getMessageKey(): string
    {
        return "REAL_EQUAL_DOMAIN_MESSAGE";
    }

    public function isSuccessful(): bool
    {
        return true;
    }
}
