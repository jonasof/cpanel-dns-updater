<?php

namespace Tests\Factories;

use Faker\Factory;
use JonasOF\CpanelDnsUpdater\Models\IP;
use JonasOF\CpanelDnsUpdater\Models\IPTypes;

class IPFactory
{
    public static function build($type = IPTypes::IPV4): IP
    {
        $faker = Factory::create();

        return new IP($type, $faker->$type);
    }
}
