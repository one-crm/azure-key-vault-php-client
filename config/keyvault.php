<?php

return [

    'credential' => [
        'endpoint' => env('IDENTITY_ENDPOINT'),
        'identity' => env('IDENTITY_HEADER', null)
    ],

    'vault_url' => env('KEYVAULT_URL'),

];