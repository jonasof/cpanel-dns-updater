<?php

namespace JonasOF\CpanelDnsUpdater;

use Desarrolla2\Cache\Cache;
use JonasOF\CpanelDnsUpdater\Exceptions\CpanelApiException;
use JonasOF\CpanelDnsUpdater\Models\IP;
use JonasOF\CpanelDnsUpdater\UpdateResult\DNSChanged;
use JonasOF\CpanelDnsUpdater\UpdateResult\ResultInterface;
use JonasOF\CpanelDnsUpdater\UpdateResult\SameIpThanRemote;
use JonasOF\CpanelDnsUpdater\UpdateResult\ZoneNotFound;
use Monolog\Logger;
use Symfony\Component\Translation\Translator;

class Updater
{
    private $config;
    private $messages;
    private $cache;
    private $logger;
    private $cpanel;

    public function __construct(
        Config $config,
        Translator $messages,
        CpanelApi $cpanel,
        Cache $cache,
        Logger $logger
    ) {
        $this->config = $config;
        $this->messages = $messages;
        $this->cpanel = $cpanel;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function updateDomains(IP $new_ip, array $subdomains)
    {
        $this->executeIfIPChanged($new_ip, function () use ($new_ip, $subdomains) {
            $shouldCacheNewIp = true;

            foreach ($subdomains as $subdomain) {
                $domain = new Models\SubdomainChange([
                    "subdomain" => $subdomain->name . ".",
                    "new_ip" => $new_ip,
                ]);

                try {
                    $result = $this->updateDomain($domain);

                    $this->logger->log(
                        $result->isSuccessful() ? 'info' : 'error',
                        $this->messages->trans($result->getMessageKey()),
                        [ "domain" => $domain ]
                    );
                } catch (CpanelApiException $e) {
                    $this->logger->error(
                        $this->messages->trans($e->getMessage()),
                        [
                            "response" => $e->response,
                            "domain" => $domain
                        ]
                    );

                    $shouldCacheNewIp = false;
                }
            }

            return $shouldCacheNewIp;
        });
    }

    private function executeIfIPChanged(IP $new_ip, callable $callback)
    {
        if (!$this->config->get('use_ip_cache')) {
            $callback();
            return;
        }

        if ($new_ip->value == $this->cache->get($new_ip->type)) {
            $this->logger->info($this->messages->trans("CACHE_EQUAL_REMOTE_MESSAGE"));
            return;
        }

        $shouldCache = $callback();

        if ($shouldCache) {
            $this->cache->set($new_ip->type, $new_ip->value);
        }
    }

    private function updateDomain(Models\SubdomainChange $subdomain): ResultInterface
    {
        $domain_info = $this->cpanel->getDomainInfo($subdomain);

        if ($domain_info === null) {
            return new ZoneNotFound();
        }

        if (!$this->config->get('force_rewrite') && $domain_info->address === $subdomain->new_ip->value) {
            return new SameIpThanRemote();
        }

        $this->cpanel->changeDnsIp($subdomain, $domain_info);

        return new DNSChanged();
    }
}
