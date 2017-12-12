<?php

namespace eLife\HypothesisClient\Credentials;

class JWTSigningCredential extends Credential
{
    private $expire;

    public function __construct(string $clientId, string $secret, int $expire = 600)
    {
        parent::__construct($clientId, $secret);
        $this->expire = $expire;
    }

    public function getExpire() : int
    {
        return $this->expire;
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
