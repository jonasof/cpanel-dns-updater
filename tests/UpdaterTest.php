<?php

namespace Tests;

use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Cache;
use JonasOF\CpanelDnsUpdater\Config;
use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Models\Domain;
use JonasOF\CpanelDnsUpdater\Updater;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;
use Tests\Mocks\FakeIPGetter;
use Hamcrest\Matchers;
use JonasOF\CpanelDnsUpdater\Models\IPTypes;
use Monolog\Logger;

class UpdaterTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function getMockedReturn()
    {
        return (object) [
            "name" => "domain1.site.com.",
            "type" => "A",
            "ttl" => "14400",
            "address" => "0.0.0.0",
            "line" => 4,
            "Line" => 4,
            "class" => "IN",
            "record" => "0.0.0.0",
            "serial_number" => "123"
        ];
    }

    public function testChangeDnsIpCallsCpanelApiWithCorrectArguments()
    {
        $api = Mockery::mock(CpanelApi::class)
            ->shouldReceive('getDomainInfo')
            ->andReturn($this->getMockedReturn())
            ->shouldReceive('changeDnsIp')
            ->once()
            ->with(Matchers::equalTo(new Domain([
                'subdomain' => 'subdomain.test.com.',
                'real_ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                'zoneType' => 'A'
            ])), "4", "123");

        $cache = new Cache(new NotCache());
        $getter = new FakeIPGetter();
        $logger = new Logger('test');
        $config = new Config([
            "url" => "https://mysite.com:2083",
            "user" => "myuser",
            "password" => "mypassword",
            "domain" => "mysite.com",
            'use_ip_cache' => false,
            'subdomains_to_update' => ['subdomain.test.com']
        ], require(__DIR__ . "/../config/config.default.php"));
        $translator = new Translator("en_US");

        $updater = new Updater($config, $translator, $api->getMock(), $cache, $getter, $logger);

        $updater->updateDomains(IPTypes::IPV4);
    }
}
