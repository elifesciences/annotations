<?php

namespace eLife\HypothesisClient\Credentials;

use Firebase\JWT\JWT;

class JWTSigningCredentials extends Credentials
{
    private $expire;

    public function __construct(string $clientId, string $secret, string $authority, int $expire = 600)
    {
        parent::__construct($clientId, $secret, $authority);
        $this->expire = $expire;
    }

    public function getExpire() : int
    {
        return $this->expire;
    }

    public function getJWT(string $username) : string
    {
        $now = $_SERVER['REQUEST_TIME'];
        $sub = "acct:{$username}@".$this->getAuthority();

        $payload = [
            'aud' => 'hypothes.is',
            'iss' => $this->getClientId(),
            'sub' => $sub,
            'nbf' => $now,
            'exp' => $now + $this->getExpire(),
        ];

        return JWT::encode($payload, $this->getSecretKey(), 'HS256');
    }

    public function toArray() : array
    {
        return parent::toArray() + [
            'expire' => $this->getExpire(),
        ];
    }

    public function unserialize($serialized)
    {
        parent::unserialize($serialized);
        $data = json_decode($serialized, true);

        $this->expire = $data['expire'];
    }
}
