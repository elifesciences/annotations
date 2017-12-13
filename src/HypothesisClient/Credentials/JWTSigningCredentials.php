<?php

namespace eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Clock\Clock;
use Firebase\JWT\JWT;

class JWTSigningCredentials extends Credentials
{
    private $clock;
    private $expire;

    public function __construct(string $clientId, string $clientSecret, string $authority, Clock $clock, int $expire = 600)
    {
        parent::__construct($clientId, $clientSecret, $authority);
        $this->clock = $clock;
        $this->expire = $expire;
    }

    public function getExpire() : int
    {
        return $this->expire;
    }

    public function getStartTime() : int
    {
        return $this->clock->time();
    }

    public function getJWT(string $username) : string
    {
        $now = $this->getStartTime();
        $sub = "acct:{$username}@".$this->getAuthority();

        $payload = [
            'aud' => 'hypothes.is',
            'iss' => $this->getClientId(),
            'sub' => $sub,
            'nbf' => $now,
            'exp' => $now + $this->getExpire(),
        ];

        return JWT::encode($payload, $this->getClientSecret(), 'HS256');
    }
}
