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

$updater = new JonasOF\CpanelDnsUpdater\CpanelDnsUpdater($config, $languages[$config->language]);
$updater->update_domains();
