<?php

namespace eLife\HypothesisClient\ApiClient;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\Psr7\build_query;

final class UsersClient
{
    use ApiClient;

    public function getUser(
        array $headers,
        string $id
    ) : PromiseInterface {
        return $this->patchRequest(
            Uri::fromParts([
                'path' => 'users/'.$id,
            ]),
            $this->getAuthorizationBasic() + $headers,
            '{}'
        );
    }

    public function createUser(
        array $headers,
        string $id,
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
                'username' => $id,
                'email' => $email,
                'display_name' => $display_name,
            ])
        );
    }

    public function updateUser(
        array $headers,
        string $id,
        string $email = null,
        string $display_name = null
    ) : PromiseInterface {
        return $this->patchRequest(
            Uri::fromParts([
                'path' => 'users/'.$id,
            ]),
            $this->getAuthorizationBasic() + $headers,
            json_encode(array_filter([
                'email' => $email,
                'display_name' => $display_name,
            ]))
        );
    }

    public function getUserToken(
        array $headers,
        string $id
    ) : PromiseInterface {
        return $this->postRequest(
            Uri::fromParts([
                'path' => 'token',
            ]),
            $headers,
            json_encode([
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $this->getCredentials()->getJWT($id),
                ],
            ])
        );
    }

    public function getUserAnnotations(
        array $headers,
        string $user,
        $token = null,
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        $group = '__world__',
        $restricted = false
    ) : PromiseInterface {
        return $this->getRequest(
            Uri::fromParts([
                'path' => 'search',
                'query' => build_query([
                    'user' => $user,
                    'group' => $group,
                    'offset' => ($page - 1) * $perPage,
                    'limit' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ]),
            ]),
            (($restricted && $token) ? ['Authorization' => 'Bearer '.$token] : []) + $headers
        );
    }
}
