<?php

use PHPUnit\Framework\TestCase;
use JonasOF\CpanelDnsUpdater\CpanelDnsUpdater;

class UpdaterTest extends TestCase
{
    public $cpanelDnsUpdater;

    public function setUp(): void
    {
        parent::setUp();

        $this->cpanelDnsUpdater = mock(CpanelDnsUpdater::class);
    }

    public function testChangeDnsIpReturnsFalseIfResponseStatusIs0()
    {
        $cpanelDnsUpdater = mock(CpanelDnsUpdater::class)
            ->makePartial()
            ->shouldReceive('get_table_of_json')
            ->andReturn(json_decode($this->mockedReturn));

        $cpanelDnsUpdater->update_domains();
    }

    protected $mockedReturn = '';
}
