<?php

namespace OneCRM\KeyVault\Traits;

use GuzzleHttp\Psr7\Request;
use OneCRM\KeyVault\Requests\DecryptRequest;
use OneCRM\KeyVault\Requests\EncryptRequest;
use OneCRM\KeyVault\Responses\KeyOperationResult;

trait HasKeys
{
    /**
     * @param EncryptRequest $encryptRequest
     * @param $keyName
     * @param $keyVersion
     * @return KeyOperationResult
     */
    public function encrypt(EncryptRequest $encryptRequest, $keyName, $keyVersion)
    {
        $request = new Request(
            'POST',
            sprintf('keys/%s/%s/encrypt?api-version=%s', $keyName, $keyVersion, self::API_VERSION),
            [],
            \GuzzleHttp\json_encode($encryptRequest->getArray())
        );
        return new KeyOperationResult($this->sendRequest($request));
    }

    /**
     * @param DecryptRequest $encryptRequest
     * @param $keyName
     * @param $keyVersion
     * @return KeyOperationResult
     */
    public function decrypt(DecryptRequest $encryptRequest, $keyName, $keyVersion)
    {
        $request = new Request(
            'POST',
            sprintf('keys/%s/%s/decrypt?api-version=%s', $keyName, $keyVersion, self::API_VERSION),
            [],
            \GuzzleHttp\json_encode($encryptRequest->getArray())
        );
        return new KeyOperationResult($this->sendRequest($request));
    }
}