<?php

chdir(__DIR__);

if (! is_file("config/config.php")) {
    echo "Create the file config/config.php before use Cpanel DNS Updater";
    exit();
}

foreach (["cache", "log"] as $dir)
    if (! is_dir($dir))
        mkdir($dir);

require_once ('libs/class.cpanel.php');
require_once ('config/config.php');
require_once ('languages.php');
require_once ('Cpanel_Dns_Updater.php');

define ("_IP_GETTER", $CDUconfig->ip_getter);
define ("_CACHE_DIR", __DIR__ . "/cache");
define ("_LOG_DIR", __DIR__ . "/log");
define ("_VERBOSE", true);

$updater = new Cpanel_Dns_Updater($CDUconfig, $CDU_LANGUAGES[$CDUconfig->language]);
$updater->update_domains();
