<?php

namespace eLife\Annotations\Credentials;

use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use Firebase\JWT\JWT;

class MockJWTSigningCredentials extends JWTSigningCredentials
{
    public function __construct()
    {
        parent::__construct('clientId', 'clientSecret', 'authority', new Clock());
    }

    public function getJWT(string $username) : string
    {
        $now = 1234567890;
        $sub = "acct:{$username}@".$this->getAuthority();

        $payload = [
            'aud' => 'hypothes.is',
            'iss' => $this->getClientId(),
            'sub' => $sub,
            'nbf' => $now,
            'exp' => $now + 600,
        ];

        return JWT::encode($payload, $this->getClientSecret(), 'HS256');
    }
}
