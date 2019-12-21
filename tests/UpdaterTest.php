<?php

namespace Tests;

use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Cache;
use JonasOF\CpanelDnsUpdater\CpanelApi;
use JonasOF\CpanelDnsUpdater\Logger;
use JonasOF\CpanelDnsUpdater\Updater;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\FakeIpGetter;

class UpdaterTest extends TestCase
{
    public function testChangeDnsIpReturnsFalseIfResponseStatusIs0()
    {
        /** @var CpanelApi $api */
        $api = $this->createMock(CpanelApi::class)
            ->method('get_domain_info')
            ->willReturn(json_decode($this->mockedReturn));
        
        $cache = new Cache(new NotCache());

        $getter = new FakeIpGetter();

        $logger = new Logger();
            
        $updater = new Updater([], [], $api, $cache, $getter, $logger);

        $cpanelDnsUpdater->update_domains();
    }

    protected $mockedReturn = '';
}
