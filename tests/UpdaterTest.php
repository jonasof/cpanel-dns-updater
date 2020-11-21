<?php

namespace Tests;

use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Cache;
use Hamcrest\Matchers;
use JonasOF\CpanelDnsUpdater\Config\Subdomain;
use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Models\SubdomainChange;
use Mockery;
use PHPUnit\Framework\TestCase;
use JonasOF\CpanelDnsUpdater\Updater;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;
use Tests\Factories\IPFactory;
use Tests\Mocks\FakeCpanelApi;
use Tests\Mocks\FakeCredentialsConfig;

function createUpdater(CpanelApi $api = null) : Updater
{
    return new Updater(
        new FakeCredentialsConfig(),
        new Translator("en_US"),
        $api ?? new FakeCpanelApi(),
        new Cache(new NotCache()),
        new Logger('test')
    );
}

class CpanelUpdaterTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testChangeDnsIpCallsCpanelApiWithCorrectArguments()
    {
        $new_ip = IPFactory::build();

        $domain_info = (object) [
            "name" => "domain1.site.com.",
            "type" => "A",
            "ttl" => "14400",
            "address" => "0.0.0.0",
            "line" => 4,
            "Line" => 4,
            "class" => "IN",
            "record" => "0.0.0.0"
        ];
    
        $api = Mockery::mock(CpanelApi::class)
            ->shouldReceive('getDomainInfo')
            ->andReturn($domain_info)
            ->shouldReceive('changeDnsIp')
            ->once()
            ->with(Matchers::equalTo(new SubdomainChange([
                'subdomain' => 'subdomain.test.com.',
                'new_ip' => $new_ip
            ])), $domain_info);

        $updater = createUpdater($api->getMock());

        $updater->updateDomains($new_ip, [new Subdomain('subdomain.test.com')]);
    }
}
