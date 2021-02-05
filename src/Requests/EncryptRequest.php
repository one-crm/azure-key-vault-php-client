<?php

namespace OneCRM\KeyVault\Requests;

use OneCRM\KeyVault\Base64UrlEncoder;

class EncryptRequest extends EncryptDecryptRequest
{
    public function getArray()
    {
        return [
            'alg' => $this->alg,
            'value' => Base64UrlEncoder::encode($this->value),
        ];
    }
}
