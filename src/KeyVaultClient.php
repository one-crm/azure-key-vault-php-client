<?php

namespace OneCRM\KeyVault;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use OneCRM\KeyVault\Exception\ClientException;

class KeyVaultClient
{
    use Traits\HasSecrets;

    const API_VERSION = '7.1';

    protected $client;

    protected $vaultUrl;

    protected $accessToken;

    public function __construct(ClientInterface $client, $vaultUrl, $accessToken)
    {
        $this->client = $client;
        $this->vaultUrl = $vaultUrl;
        $this->accessToken = $accessToken;
    }

    public function setVaultUrl($vaultUrl)
    {
        return static::make($vaultUrl, $this->accessToken);
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    public static function make(string $vaultUrl, string $accessToken)
    {
        $client = new Client([
            'base_uri' => $vaultUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ]
        ]);

        return new static($client, $vaultUrl, $accessToken);
    }

    private function sendRequest(Request $request)
    {
        try {
            $response = $this->client->send($request);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->handleRequestException($e);
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function handleRequestException(GuzzleException $e)
    {
        if (! $e->getResponse() || ! is_a($e->getResponse(), Response::class)) {
            return;
        }

        $response = $e->getResponse();

        try {
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e2) {
            throw new ClientException(trim($e->getMessage()), $response->getStatusCode(), $e);
        }

        if (! empty($data['error']) && ! empty($data['error']['message']) && ! empty($data['error']['code'])) {
            throw new ClientException(
                trim($data['error']['code'] . ': ' . $data['error']['message']),
                $response->getStatusCode(),
                $e
            );
        }

        if (!empty($data['error']) && is_scalar($data['error'])) {
            throw new ClientException(
                trim('Request failed with error: ' . $data['error']),
                $response->getStatusCode(),
                $e
            );
        }
    }
}
