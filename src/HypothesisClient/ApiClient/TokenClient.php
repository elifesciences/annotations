<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;

final class TokenClient
{
    use ApiClient;

    private $credentials;

    public function __construct(HttpClient $httpClient, JWTSigningCredentials $credentials, array $headers = [])
    {
        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'HypothesisClient');
        $this->headers = $headers;
        $this->credentials = $credentials;
    }

    /**
     * @return JWTSigningCredentials|null
     */
    private function getCredentials()
    {
        return $this->credentials;
    }

    public function getToken(
        array $headers,
        string $username
    ) : PromiseInterface {
        $jwt = $this->getCredentials()->getJWT($username);
        return $this->postRequest(
            Uri::fromParts([
                'path' => 'token',
            ]),
            $headers,
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ])
        );
    }
}
