<?php

$container = (new DI\ContainerBuilder())->build();

$config = (object) require ('config/config.php');
$languages = require ('languages.php');

$container->set('config', $config);
$container->set('messages', $languages[$config->language]);

$container->set(Gufy\CpanelPhp\Cpanel::class, buildCpanel($container));
$container->set(Desarrolla2\Cache\Cache::class, buildCache($container));

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

return $container;