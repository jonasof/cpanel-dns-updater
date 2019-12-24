<?php

// override this parameters on config.php

return [
    // Url that you use to connect to cpanel (see the address in cpanel login page)
    // put here https://{hostname}:{port}
    // I dont know if this will work with invalid certificates names
    "url" => "https://mysite.com:2083",

    // Your cpanel user
    "user" => "myuser",

    // Your cpanel password. You can hack here if you dont want to store in plain text
    // I would be happier if I can use other authentication method like keys, but it seems
    // that cpanel APIs only accept password in plain text
    "password" => "mypassword",

    // Put your subdomains to update here
    "subdomains_to_update" => [], // example: ["host1.mysite.com", "host2.mysite.com"]

    // The base of above subdomains. It's required to fill this parameter!
    "domain" => "mysite.com",

    // Enable caching your IP so only perform requests to your api when your IP changes.
    // It's a good idea to it to avoiding making many requests to your cPanel
    "use_ip_cache" => false,

    // Cache directory
    "cache_dir" => __DIR__ . "/../cache",

    // Cache time to live in ms. Defaults to maximum (infinite)
    "cache_ttl" => PHP_INT_MAX,

    // app language (en_US or pt_BR)
    "language" => "en_US",

    // Http connection timeout in seconds
    "connection_timeout" => 10,

    // log directory
    "log_dir" => __DIR__ . "/../log",

    // print errors
    "print_errors" => false,

    // Force POST request for ip assigment in cPanel, even if ip is the same
    // (usually not needed except for development propouses)
    "force_rewrite" => false,

    "modes" => [
        // Enalbe IPv4 A registers
        "ipv4" => true,
        // put here one trustable site that you can get your ip in plain text.
        // some examples: http://ipinfo.io/ip http://www.trackip.net/ip http://curlmyip.com/
        // if you prefer, create an external php server with a
        // file "<?php echo $_SERVER['REMOTE_ADDR'];" to provide this service
        "ipv4_getter" => "http://ipinfo.io/ip",

        //Enalbe IPv6 AAAA registers
        "ipv6" => true,
        "ipv6_getter" => "https://wtfismyip.com/text",
    ]
];
