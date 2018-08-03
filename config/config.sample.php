<?php

//copy or rename this to config.php

return [
    // url that you use to connect to cpanel (see the address in cpanel login page)
    // put here http(s)://{hostname}:{port}
    // i dont know if this works with invalid certificates names
    "url" => "https://mysite.com:2083",

    //your cpanel user
    "user" => "myuser",

    //your cpanel password. You can hack here if you dont want to store in plain text
    //i will be happier if i can use other authentication method like keys
    "password" => "mypassword",

    //put your subdomains to update here!!
    "subdomains_to_update" => ["host1.mysite.com", "host2.mysite.com"],

    //this is the base of subdomains above. Its your site domain - you need fill this!
    "domain" => "mysite.com",

    // cache your ip in /cache/ip, avoiding many requests to your cpanel site
    "use_ip_cache" => false,

    // cache time to live
    "cache_ttl" => 31104000,

    // app language
    "language" => "EN",

    // http connection timeout
    "connection_timeout" => 2,

    "modes" => [
        //Enalbe IPv4 A registers
        "ipv4" => true,
        //put here one trusted site that you can get your ip in plain text.
        //some examples: http://ipinfo.io/ip http://www.trackip.net/ip http://curlmyip.com/
        //if you prefer, put an file with "<?php echo $_SERVER['REMOTE_ADDR'];" to provide this service
        "ipv4_getter" => "http://ipinfo.io/ip",

        //Enalbe IPv6 AAAA registers
        "ipv6" => true,
        "ipv6_getter" => "https://wtfismyip.com/text",
    ]
];
