<?php

namespace JonasOF\CpanelDnsUpdater\UpdateResult;

class ZoneNotFound implements ResultInterface
{
    public function getMessageKey(): string
    {
        return "ZoneNotFound";
    }

    public function isSuccessful(): bool
    {
        return true;
    }
}
