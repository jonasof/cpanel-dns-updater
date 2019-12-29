<?php

namespace Tests;

use Hamcrest\Matchers;
use JonasOF\CpanelDnsUpdater\CpanelApi;
use Mockery;
use PHPUnit\Framework\TestCase;
use Gufy\CpanelPhp\CpanelInterface;
use JonasOF\CpanelDnsUpdater\Models\SubdomainChange;
use Tests\Factories\IPFactory;
use Tests\Mocks\FakeCredentialsConfig;

function createCpanelApiForFindDomain() : CpanelApi
{
    $api = Mockery::mock(CpanelInterface::class)
        ->shouldReceive('cpanel')
        ->once()
        ->with(Matchers::anything(), "fetchzone", Matchers::anything(), Matchers::anything())
        ->andReturn(file_get_contents(__DIR__ . "/../docs/sampleCpanelGetZonesResponse.json"));

    /** @var CpanelInterface $cpanel  */
    $cpanel = $api->getMock();

    return new CpanelApi(new FakeCredentialsConfig(), $cpanel);
}

class CpanelApiFindDomainTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testFindDomainInGetDomainInfo()
    {
        $response = createCpanelApiForFindDomain()->getDomainInfo(new SubdomainChange([
            "subdomain" => "domain1.site.com.",
            "new_ip" => IPFactory::build(),
        ]));

        $this->assertEquals((object) [
            "name" => "domain1.site.com.",
            "type" => "A",
            "ttl" => "14400",
            "address" => "0.0.0.0",
            "line" => 4,
            "Line" => 4,
            "class" => "IN",
            "record" => "0.0.0.0"
        ], $response);
    }

    public function testNotFindDomainInGetDomainInfo()
    {
        $response = createCpanelApiForFindDomain()->getDomainInfo(new SubdomainChange([
            "subdomain" => "inexistent.domain.com.",
            "new_ip" => IPFactory::build(),
        ]));

        $this->assertEquals(null, $response);
    }
}
