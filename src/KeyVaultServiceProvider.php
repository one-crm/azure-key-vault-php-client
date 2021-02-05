<?php

namespace OneCRM\KeyVault;

use OneCRM\KeyVault\CredentialFactory;
use Illuminate\Support\ServiceProvider;

class KeyVaultServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('azure.auth', CredentialFactory::class);

        $this->app->bind('azure.keyvault', function ($app) {

            $credential = $app['azure.auth']->make(
                config('keyvault.credential') + ['resource' => 'https://vault.azure.net']
            );

            return KeyVaultClient::make(
                config('keyvault.vault_url'), $credential['access_token'],
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/keyvault.php' => config_path('keyvault.php'),
        ]);
    }
}