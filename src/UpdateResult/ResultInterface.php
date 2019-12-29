<?php

namespace JonasOF\CpanelDnsUpdater\UpdateResult;

interface ResultInterface
{
    public function getMessageKey(): string;
    public function isSuccessful(): bool;
}
