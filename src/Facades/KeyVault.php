<?php

namespace OneCRM\KeyVault\Facades;

class KeyVault extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'azure.keyvault';
    }
}
