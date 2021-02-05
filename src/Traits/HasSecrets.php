<?php

namespace OneCRM\KeyVault\Traits;

use GuzzleHttp\Psr7\Request;
use OneCRM\KeyVault\Responses\SecretBundle;
use OneCRM\KeyVault\Requests\SetSecretRequest;
use OneCRM\KeyVault\Responses\SecretListResult;
use OneCRM\KeyVault\Responses\DeletedSecretBundle;

trait HasSecrets
{
    /**
     * @param SetSecretRequest $setSecretRequest
     * @param string $secretName
     * @return SecretBundle
     */
    public function setSecret(SetSecretRequest $setSecretRequest, $secretName)
    {
        $request = new Request(
            'PUT',
            sprintf('secrets/%s?api-version=%s', $secretName, self::API_VERSION),
            [],
            json_encode($setSecretRequest->getArray())
        );
        return new SecretBundle($this->sendRequest($request));
    }

    /**
     * @param string $secretName
     * @param string $secretVersion
     * @return SecretBundle
     */
    public function getSecret($secretName, $secretVersion = null)
    {
        if ($secretVersion === null) {
            $request = new Request(
                'GET',
                sprintf('secrets/%s?api-version=%s', $secretName, self::API_VERSION)
            );
        } else {
            $request = new Request(
                'GET',
                sprintf('secrets/%s/%s?api-version=%s', $secretName, $secretVersion, self::API_VERSION)
            );
        }
        return new SecretBundle($this->sendRequest($request));
    }


    /**
     * @param int $maxResults
     * @return SecretListResult
     */
    public function getSecrets($maxResults = self::DEFAULT_PAGE_SIZE)
    {
        $request = new Request(
            'GET',
            sprintf('secrets/?maxresults=%s&api-version=%s', $maxResults, self::API_VERSION)
        );
        return new SecretListResult($this->sendRequest($request));
    }

    /**
     * @param int $pageSize
     * @return SecretItem[]
     */
    public function getAllSecrets($pageSize = self::DEFAULT_PAGE_SIZE)
    {
        $listResult = $this->getSecrets($pageSize);
        $items = $listResult->getValue();
        while ($listResult->getNextLink()) {
            $request = new Request(
                'GET',
                $listResult->getNextLink()
            );
            $listResult = new SecretListResult($this->sendRequest($request));
            $items = array_merge($items, $listResult->getValue());
        }
        return $items;
    }

    /**
     * @param $secretName
     * @return DeletedSecretBundle
     */
    public function deleteSecret($secretName)
    {
        $request = new Request(
            'DELETE',
            sprintf('secrets/%s?api-version=%s', $secretName, self::API_VERSION)
        );
        return new DeletedSecretBundle($this->sendRequest($request));
    }
}