<?php

use Desarrolla2\Cache\Adapter\File;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Gufy\CpanelPhp\Cpanel;
use Desarrolla2\Cache\Cache;
use DI\ContainerBuilder;
use JonasOF\CpanelDnsUpdater\IPGetter;
use JonasOF\CpanelDnsUpdater\Config;
use JonasOF\CpanelDnsUpdater\HttpIPGetter;
use Psr\Container\ContainerInterface;

$buildConfig = function () {
    $config = (object) include 'config/config.php';

    return new Config($config);
};

$buildCpanel = function (ContainerInterface $c) {
    $config = $c->get(Config::class);
    
    $cpanel = new Gufy\CpanelPhp\Cpanel(
        [
        "host" => $config->get('url'),
        "username" => $config->get('user'),
        "password" => $config->get('password'),
        "auth_type" => "password",
        ]
    );

    $cpanel->setConnectionTimeout($config->get('connection_timeout'));

    return $cpanel;
};

$buildCache = function (ContainerInterface $c) {
    $config = $c->get(Config::class);

    $adapter = new File(_CACHE_DIR);
    $adapter->setOption('ttl', $config->get('cache_ttl'));

    return new Cache($adapter);
};

$buildLanguages = function (ContainerInterface $c) {
    $config = $c->get(Config::class);
    
    $languages = include 'languages.php';

    $translator = new Translator($config->get('language'));
    $translator->addLoader('array', new ArrayLoader());
    $translator->addResource('array', $languages['EN'], 'en_US');
    $translator->addResource('array', $languages['PT_BR'], 'pt_BR');

    return $translator;
};

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions(
    [
    Config::class => \DI\factory($buildConfig),
    Cpanel::class => \DI\factory($buildCpanel),
    Cache::class => \DI\factory($buildCache),
    Translator::class => \DI\factory($buildLanguages),
    IPGetter::class => \DI\autowire(HttpIPGetter::class),
    ]
);


return $containerBuilder->build();
