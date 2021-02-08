<?php

namespace OneCRM\KeyVault;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use OneCRM\KeyVault\Responses\SecretBundle;
use OneCRM\KeyVault\Requests\SetSecretRequest;
use OneCRM\KeyVault\Responses\SecretListResult;
use OneCRM\KeyVault\Responses\DeletedSecretBundle;

class FakeKeyVaultClient
{
    protected $logger;

    protected $fakeSecrets;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setFakeSecrets(array $secrets)
    {
        $this->secrets = $secrets;

        return $this;
    }

    public function setSecret(SetSecretRequest $setSecretRequest, $secretName)
    {
        $this->logger->info('KeyVault Set: ' . json_encode($setSecretRequest->getArray()));

        return $this->getSecret($secretName);
    }

    public function getSecret($secretName, $secretVersion = null)
    {
        $version = $secretVersion ?? strtolower(Str::random(32));

        return new SecretBundle([
            'value' => $this->secrets[$secretName] ?? Str::random(20),
            'id' => $this->vaultUrl("/secrets/$secretName/$version"),
            'attributes' => [
                'enabled' => true,
                'created' => time(),
                'updated' => time(),
                'recoveryLevel' => 'Recoverable+Purgeable',
            ],
        ]);
    }

    public function getSecrets($maxResults = self::DEFAULT_PAGE_SIZE)
    {
        return new SecretListResult([
            'value' => array_map(function ($value, $secret) {
                return [
                    'contentType' => 'plainText',
                    'id' => $this->vaultUrl("/secrets/$secret"),
                    'attributes' => [
                        'enabled' => true,
                        'created' => time(),
                        'updated' => time(),
                    ],
                ];
            }, $this->secrets),
            'nextLink' => $this->vaultUrl('/secrets?api-version=7.1&maxresults=1'),
        ]);
    }

    public function getAllSecrets($pageSize = self::DEFAULT_PAGE_SIZE)
    {
        return $this->getSecrets()->getValue();
    }

    public function deleteSecret($secretName)
    {
        $this->logger->info('KeyVault Deleted: ' . $secretName);

        $version = strtolower(Str::random(32));

        return new DeletedSecretBundle([
            'recoveryId' => $this->vaultUrl("/deletedsecrets/$secretName"),
            'deletedDate' => time(),
            'scheduledPurgeDate' => time(),
            'id' => $this->vaultUrl("/secrets/$secretName/$version"),
            'attributes' => [
                'enabled' => true,
                'created' => time(),
                'updated' => time(),
                'recoveryLevel' => 'Recoverable+Purgeable',
            ],
        ]);
    }

    protected function vaultUrl($path = '/')
    {
        return rtrim(config('keyvault.vault_url')) . '/' . ltrim($path, '/');
    }
}
