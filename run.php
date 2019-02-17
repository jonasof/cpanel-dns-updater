<?php

chdir(__DIR__);

if (! is_file("config/config.php")) {
    echo "Create the file config/config.php before use Cpanel DNS Updater";
    exit();
}

if (! is_file("vendor/autoload.php")) {
    echo "Please run 'composer install' before use Cpanel DNS Updater";
    exit();
}

foreach (["cache", "log"] as $dir)
    if (! is_dir($dir))
        mkdir($dir);

require_once ('vendor/autoload.php');

$config = (object) require ('config/config.php');
$languages = require ('languages.php');

define ("_CACHE_DIR", __DIR__ . "/cache");
define ("_LOG_DIR", __DIR__ . "/log");
define ("_VERBOSE", true);

$container = (new DI\ContainerBuilder())->build();

$container->set('config', $config);
$container->set('messages', $languages[$config->language]);

$container->set(Gufy\CpanelPhp\Cpanel::class, buildCpanel($container));
$container->set(Desarrolla2\Cache\Cache::class, buildCache($container));

$updater = $container->get(JonasOF\CpanelDnsUpdater\Updater::class);
$updater->update_domains();

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
    $container->get('config');

    $adapter = new File(_CACHE_DIR);
    $adapter->setOption('ttl', $container->cache_ttl);

    return new Cache($adapter);
}