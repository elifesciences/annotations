<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\json_encode;

final class UsersClient
{
    use ApiClient;

    private $credentials;

    public function __construct(HttpClient $httpClient, UserManagementCredentials $credentials = null, array $headers = [])
    {
        if ($credentials) {
            $headers['Authorization'] = $credentials->getAuthorizationBasic();
        }

        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'HypothesisClient');
        $this->headers = $headers;
        $this->credentials = $credentials;
    }

    public function getUser(
        array $headers,
        string $username
    ) : PromiseInterface {
        return $this->patchRequest(
            Uri::fromParts([
                'path' => 'users/'.$username,
            ]),
            $headers,
            '{}'
        );
    }

    public function createUser(
        array $headers,
        string $username,
        string $email,
        string $display_name
    ) : PromiseInterface {
        return $this->postRequest(
            Uri::fromParts([
                'path' => 'users',
            ]),
            $headers,
            json_encode([
                'authority' => $this->credentials->getAuthority(),
                'username' => $username,
                'email' => $email,
                'display_name' => $display_name,
            ])
        );
    }

    public function updateUser(
        array $headers,
        string $username,
        string $email = null,
        string $display_name = null
    ) : PromiseInterface {
        return $this->patchRequest(
            Uri::fromParts([
                'path' => 'users/'.$username,
            ]),
            $headers,
            json_encode(array_filter([
                'email' => $email,
                'display_name' => $display_name,
            ]))
        );
    }
}
