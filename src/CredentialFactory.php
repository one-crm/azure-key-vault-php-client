<?php

namespace OneCRM\KeyVault;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Cache\Store as Cache;

class CredentialFactory
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function make(array $config)
    {
        $authMethod = $this->determineAuthenticationMethod($config);

        $cacheName = "azure.auth.$authMethod";

        if ($cached = $this->cache->get($cacheName)) {
            return $cached['access_token'];
        }

        $authenticator = 'make' . Str::studly($authMethod) . 'Authenticator';

        if (! method_exists($this, $authenticator)) {
            throw new InvalidArgumentException('Unable to determine authentication method.');
        }

        $auth = $this->{$authenticator}($config)->getAuthenticationPayload();

        return tap($auth, function ($auth) use ($cacheName) {
            $this->cache->put($cacheName, Carbon::createFromTimestamp($auth['expires_on']), $auth);
        });
    }

    protected function makeManagedIdentityAuthenticator(array $config)
    {
        return new Authenticator\ManagedIdentityAuthenticator(
            $config['resource'], $config['endpoint'], $config['identity']
        );
    }

    protected function determineAuthenticationMethod(array $config)
    {
        if (isset($config['tenant_id'], $config['client_id'], $config['client_secret'])) {
            return 'client-credential';
        }
        if (isset($config['endpoint'], $config['identity'])) {
            return 'managed-identity';
        }
        throw new InvalidArgumentException('Unknown Azure KeyVault Authentication.');
    }
}