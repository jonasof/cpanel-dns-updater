<?php

//copy or rename this to config.php

$CDUconfig = new stdClass();

//hostname that you use to connect to cpanel (see the address in cpanel login page)
$CDUconfig->hostname = "cpanel.mysite.com";

//your cpanel user
$CDUconfig->user = "myuser";

//your cpanel password. You can hack here if you dont want to store in plain text
//i will be happier if i can use other authentication method like keys
$CDUconfig->password = "mypassword";

//put your subdomains to update here!!
$CDUconfig->subdomains_to_update = ["host1.mysite.com", "host2.mysite.com"];

//this is the base of subdomains above. Its your site domain - you need fill this!
$CDUconfig->domain = "mysite.com";

//put here one trust site that you can get your ip in plain text.
//some examples: http://ipinfo.io/ip http://www.trackip.net/ip http://curlmyip.com/
//if you prefer, put an file with "<?php echo $_SERVER['REMOTE_ADDR'];" to provide this service 
$CDUconfig->ip_getter = "http://ipinfo.io/ip";

// cache your ip in /cache/ip, avoiding many requests to your cpanel site
$CDUconfig->use_ip_cache = false;

// app language
$CDUconfig->language = "EN";