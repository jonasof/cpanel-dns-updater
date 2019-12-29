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

function createCpanelApiForChangeIp() : CpanelApi
{
    $api = Mockery::mock(CpanelInterface::class)
        ->shouldReceive('cpanel')
        ->once()
        ->with(Matchers::anything(), "edit_zone_record", Matchers::anything(), Matchers::anything())
        ->andReturn(file_get_contents(__DIR__ . "/../docs/sampleCpanelEditZoneResponse.json"));

    /** @var CpanelInterface $cpanel  */
    $cpanel = $api->getMock();

    return new CpanelApi(new FakeCredentialsConfig(), $cpanel);
}

class CpanelApiChangeIpTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testFindDomainInGetDomainInfo()
    {
        $response = createCpanelApiForChangeIp()->changeDnsIp(new SubdomainChange([
            "subdomain" => "domain1.site.com.",
            "new_ip" => IPFactory::build(),
        ]), (object) [
            "name" => "domain1.site.com.",
            "type" => "A",
            "ttl" => "14400",
            "address" => "0.0.0.0",
            "line" => 4,
            "Line" => 4,
            "class" => "IN",
            "record" => "0.0.0.0"
        ]);

        $this->assertStringContainsString("Bind reloading on domain using rndc zone", $response);
    }
}
