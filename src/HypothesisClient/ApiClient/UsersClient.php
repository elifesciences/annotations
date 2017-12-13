<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;

final class UsersClient
{
    use ApiClient;

    private $credentials;

    public function __construct(HttpClient $httpClient, UserManagementCredentials $credentials = null, array $headers = [])
    {
        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'HypothesisClient');
        $this->headers = $headers;
        $this->credentials = $credentials;
    }

    /**
     * @return UserManagementCredentials|null
     */
    private function getCredentials()
    {
        return $this->credentials;
    }

    private function getAuthorizationBasic() : array
    {
        return ($this->getCredentials() instanceof UserManagementCredentials) ? ['Authorization' => $this->getCredentials()->getAuthorizationBasic()] : [];
    }

    public function getUser(
        array $headers,
        string $username
    ) : PromiseInterface {
        return $this->patchRequest(
            Uri::fromParts([
                'path' => 'users/'.$username,
            ]),
            $this->getAuthorizationBasic() + $headers,
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
            $this->getAuthorizationBasic() + $headers,
            json_encode([
                'authority' => $this->getCredentials()->getAuthority(),
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
            $this->getAuthorizationBasic() + $headers,
            json_encode(array_filter([
                'email' => $email,
                'display_name' => $display_name,
            ]))
        );
    }
}
