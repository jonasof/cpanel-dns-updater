<?php

namespace JonasOF\CpanelDnsUpdater\Models;

class IP
{
    public $type;
    public $value;

    public function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getDNSType()
    {
        return IPTypes::getDNSTypeFromIpType($this->type);
    }
}
