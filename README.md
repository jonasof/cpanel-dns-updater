CPANEL DNS UPDATER
==================

[Descrição em português](LEIAME.md)

This scripts update the IP of the client subdomain with a server running cPanel (like no-ip)

Example: imagine that you own one website with cPanel, in the domain 
<mydomain.com>, and you want to point one subdomain to some network like your 
home <home.mydomain.com>

If this network uses static IP, you need just create an subdomain in "Simple 
DNS zone Editor" type A. But if this point is dynamic IP, you need one script to
update this value periodically, and this script do this.

This script can be a good replacement to the service noip.com

Requirements
--------

PHP >= 5.4 (maybe works in 5.3)

How to use
---------

Pass 1: create one subdomain in cPanel type A with yout current IP (in DNS Zone 
Editor).

Pass 2: clone/download this repository.

Pass 3: install dependencies with composer (composer install)

Pass 4: put the files in any desidered folder. I recommend 
/opt/cpanel-dns-updater. If you want, you can put it in a web folder and run 
run.php.

Pass 5: create the file config/config.php. Insert the data required in 
config/config.sample.php

Pass 6: configure an cron to run.php (asset run with the same owner of folder). 
Exemple:
0,30 * * * * /usr/bin/php /opt/cpanel-dns-updater/run.php 


Libraries
-----------
This script uses the API "cpanel-php" <https://github.com/mgufrone/cpanel-php/tree/master> 
from mgufrone.


History
--------

I created this script after the shutdown of no-ip service by Microsoft:
http://www.noip.com/blog/2014/07/10/microsoft-takedown-details-updates/

I had an CPANEL site, so i have the idea to use it DNS Server. After some days 
i created this script and i published one year after (before i dont known GIT).

License
-------

The MIT License (MIT)

Copyright (c) 2014, 2015 JonasOF

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Contact
-------

email: contato@jonasof.com.br 