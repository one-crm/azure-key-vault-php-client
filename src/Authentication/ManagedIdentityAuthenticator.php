<?php

namespace OneCRM\KeyVault\Authentication;

use Illuminate\Support\Facades\Http;
use OneCRM\KeyVault\Exception\InvalidResponseException;

class ManagedIdentityAuthenticator implements AuthenticatorInterface
{
    const IDENTITY_METADATA_ENDPOINT = 'http://169.254.169.254/metadata/identity/oauth2/token';

    const API_VERSION = '2019-11-01';

    private $resource;

    private $endpoint;

    private $identity;

    public function __construct($resource, $endpoint = null, $identity = null)
    {
        $this->resource = $resource;
        $this->identity = $identity;
        $this->endpoint = $endpoint ?? static::IDENTITY_METADATA_ENDPOINT;
    }

    public function getAuthenticationPayload()
    {
        $endpoint = $this->endpoint;
        $identityHeader = $this->identity ? 'X-IDENTITY-HEADER' : 'Metadata';
        $identityValue = $this->identity ?? 'true';

        $response = Http::withHeaders([
            $identityHeader => $identityValue
        ])->get($endpoint, [
            'api-version' => static::API_VERSION,
            'format' => 'text',
            'resource' => $this->resource
        ]);

        if ($response->failed() || empty($data['access_token'])) {
            throw new InvalidResponseException(
                'Access token not provided in response: ' . $response->body()
            );
        }

        return $response->json();
    }
}
