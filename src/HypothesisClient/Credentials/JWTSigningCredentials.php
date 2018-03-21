<?php

namespace eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Clock\Clock;
use Firebase\JWT\JWT;

final class JWTSigningCredentials extends Credentials
{
    private $clock;
    private $expire;

    public function __construct(string $clientId, string $clientSecret, string $authority, Clock $clock, int $expire = 600)
    {
        parent::__construct($clientId, $clientSecret, $authority);
        $this->clock = $clock;
        $this->expire = $expire;
    }

    public function getJWT(string $username) : string
    {
        $now = $this->clock->time();
        $sub = "acct:{$username}@{$this->getAuthority()}";

        $payload = [
            'aud' => 'hypothes.is',
            'iss' => $this->getClientId(),
            'sub' => $sub,
            'nbf' => $now,
            'exp' => $now + $this->expire,
        ];

        return JWT::encode($payload, $this->getClientSecret(), 'HS256');
    }
}
