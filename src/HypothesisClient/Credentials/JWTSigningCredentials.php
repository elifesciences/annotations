<?php

namespace eLife\HypothesisClient\Credentials;

use Firebase\JWT\JWT;

class JWTSigningCredentials extends Credentials
{
    private $expire;
    private $startTime;

    public function __construct(string $clientId, string $clientSecret, string $authority, int $expire = 600, int $startTime = null)
    {
        parent::__construct($clientId, $clientSecret, $authority);
        $this->expire = $expire;
        $this->startTime = $startTime;
    }

    public function getExpireTime() : int
    {
        return $this->getStartTime() + $this->expire;
    }

    public function getStartTime() : int
    {
        $this->startTime = $this->startTime ?? time();

        return $this->startTime;
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
            'exp' => $this->getExpireTime(),
        ];

        return JWT::encode($payload, $this->getClientSecret(), 'HS256');
    }

    public function toArray() : array
    {
        return parent::toArray() + [
            'expire' => $this->expire,
        ];
    }

    public function unserialize($serialized)
    {
        parent::unserialize($serialized);
        $data = json_decode($serialized, true);

        $this->expire = $data['expire'];
    }
}
