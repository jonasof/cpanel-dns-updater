<?php

use Desarrolla2\Cache\Adapter\File;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Gufy\CpanelPhp\Cpanel;
use Desarrolla2\Cache\Cache;
use DI\ContainerBuilder;
use JonasOF\CpanelDnsUpdater\IPGetter;
use DI\Container;
use JonasOF\CpanelDnsUpdater\HttpIPGetter;

/** @var Container $container */
$container = (new ContainerBuilder())->useAnnotations(false)->build();

$config = (object) require('config/config.php');

$container->set('config', $config);

$container->set(Cpanel::class, buildCpanel($container));
$container->set(Cache::class, buildCache($container));
$container->set(Translator::class, buildCache($container));
$container->set(IPGetter::class, \DI\create(HttpIPGetter::class));

function buildCpanel($container) {
    $config = $container->get('config');

    $cpanel = new Gufy\CpanelPhp\Cpanel([
        "host" => $config->url,
        "username" => $config->user,
        "password" => $config->password,
        "auth_type" => "password",
    ]);

    $cpanel->setConnectionTimeout($config->connection_timeout);

    return $cpanel;
}

function buildCache($container) {
    $config = $container->get('config');

    $adapter = new File(_CACHE_DIR);
    $adapter->setOption('ttl', $config->cache_ttl);

    return new Cache($adapter);
}

function buildLanguages($container) {
    $languages = require('languages.php');

    $translator = new Translator($container->get('config')->language);
    $translator->addLoader('array', new ArrayLoader());
    $translator->addResource('array', $languages['EN'], 'en_US');
    $translator->addResource('array', $languages['PT_BR'], 'pt_BR');

    return $translator;
}

return $container;
