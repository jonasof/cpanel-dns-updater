<?php

namespace Tests;

use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Cache;
use Hamcrest\Matchers;
use JonasOF\CpanelDnsUpdater\Config;
use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Models\IP;
use JonasOF\CpanelDnsUpdater\Models\SubdomainChange;
use Mockery;
use PHPUnit\Framework\TestCase;
use JonasOF\CpanelDnsUpdater\Updater;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

class CpanelUpdaterTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function getMockedDomainInfo()
    {
        return (object) [
            "name" => "domain1.site.com.",
            "type" => "A",
            "ttl" => "14400",
            "address" => "0.0.0.0",
            "line" => 4,
            "Line" => 4,
            "class" => "IN",
            "record" => "0.0.0.0"
        ];
    }

    public function testChangeDnsIpCallsCpanelApiWithCorrectArguments()
    {
        $domain = new SubdomainChange([
            'subdomain' => 'subdomain.test.com.',
            'new_ip' => new IP('ipv4', '90.112.128.170')
        ]);

        $domain_info = $this->getMockedDomainInfo();

        $api = Mockery::mock(CpanelApi::class)
            ->shouldReceive('getDomainInfo')
            ->andReturn($domain_info)
            ->shouldReceive('changeDnsIp')
            ->once()
            ->with(Matchers::equalTo($domain), $domain_info);

        $cache = new Cache(new NotCache());
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

        $updater = new Updater($config, $translator, $api->getMock(), $cache, $logger);

        $updater->updateDomains(new IP("ipv4", "90.112.128.170"), ['subdomain.test.com']);
    }
}
