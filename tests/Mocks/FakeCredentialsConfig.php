<?php

namespace Tests\Mocks;

use JonasOF\CpanelDnsUpdater\Config;

class FakeCredentialsConfig extends Config
{
    protected $fake_credentials = [
        "url" => "https://mysite.com:2083",
        "user" => "myuser",
        "password" => "mypassword",
        "domain" => "mysite.com",
        'use_ip_cache' => false,
        'subdomains_to_update' => ['fake.domain.com']
    ];

    public function __construct(array $config = [])
    {
        $config = array_merge($this->fake_credentials, $config);

        parent::__construct($config, require(__DIR__ . "/../../config/config.default.php"));
    }
}
