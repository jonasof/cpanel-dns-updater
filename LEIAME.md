CPANEL DNS UPDATER
==================

Este script atualiza o ip de um cliente com um servidor CPANEL (como no-ip).

Por exemplo, imagine que você possua um site com hospedagem CPANEL no domínio 
<mydomain.com> e você queira apontar um subdomínio para alguma rede como sua 
casa <home.mydomain.com>

Se o seu IP for estático, basta criar um subdomínio em "Simple DNS zone 
Editor" tipo A. Mas se este ponto possui IP dinâmico, você precisa de um script 
para atualizar este valor periodicamente - e é o que este faz.

Este pode ser um bom substituto ao serviço noip.com.

Requisitos
--------

PHP >= 5.4 (talvez funcione com 5.3)

Como usar
---------

Passo 1: crie um subdomínio no cPanel tipo A com o seu ip atual (DNS Zone Editor).

Passo 2: clone o repositório ou baixe ao lado.

Passo 3: instale as dependências com composer (composer install)

Passo 4: coloque os arquivos em qualquer pasta desejada. Recomendo 
/opt/cpanel-dns-updater. Você também pode colocar numa pasta web e executar 
run.php.

Passo 5: crie o arquivo config/config.php. Insira os dados solicitados no modelo 
config/config.sample.php

Passo 6: configure um cron para run.php (certifique-se de rodar com o mesmo 
dono da pasta). Exemplo:
0,30 * * * * /usr/bin/php /opt/cpanel-dns-updater/run.php 


Bibliotecas
-----------
Este script usa a API "cpanel-php" <https://github.com/mgufrone/cpanel-php/tree/master> 
de mgufrone.


História
--------

A motivação para criá-lo foi a derrubada do serviço da no-ip
pela microsoft durante alguns dias:
http://www.noip.com/blog/2014/07/10/microsoft-takedown-details-updates/

Eu tinha um site com CPANEL, então tive a ideia de usá-lo como servidor de DNS 
dinâmico. Alguns dias depois eu criei este script. Um ano depois decidi 
publicá-lo (antes não conhecia GIT).


Licença
-------

The MIT License (MIT)

Copyright (c) 2014, 2015, 2018, 2019 (MIT License)

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

Contato
-------

email: contato@jonasof.com.br 