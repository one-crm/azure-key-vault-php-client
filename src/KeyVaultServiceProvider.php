<?php

namespace OneCRM\KeyVault;

use OneCRM\KeyVault\CredentialFactory;
use Illuminate\Support\ServiceProvider;

class KeyVaultServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('azure.credential', CredentialFactory::class);

        $this->app->bind('azure.keyvault', function ($app) {

            $credential = $app['azure.credential']->make(
                config('keyvault.credential') + ['resource' => 'https://keyvault.azure.net']
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