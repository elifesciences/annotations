<?php

namespace eLife\HypothesisClient\Credentials;

use Firebase\JWT\JWT;

class Credentials
{
    private $userManagement;
    private $jwtSigning;
    private $authority;
    private $group;

    public function __construct(UserManagementCredential $userManagement, JWTSigningCredential $jwtSigning, string $authority, string $group)
    {
        $this->userManagement = $userManagement;
        $this->jwtSigning = $jwtSigning;
        $this->authority = trim($authority);
        $this->group = trim($group);
    }

    public function userManagement() : UserManagementCredential
    {
        return $this->userManagement;
    }

    public function jwtSigning() : JWTSigningCredential
    {
        return $this->jwtSigning;
    }

    public function getAuthority() : string
    {
        return $this->authority;
    }

    public function getGroup() : string
    {
        return $this->group;
    }

    public function getAuthorizationBasic() : string
    {
        return 'Basic '.base64_encode($this->userManagement()->getClientId().':'.$this->userManagement()->getSecretKey());
    }

    public function getJWT($id)
    {
        $now = time();
        $sub = "acct:{$id}@".$this->getAuthority();

        $payload = [
            'aud' => 'hypothes.is',
            'iss' => $this->jwtSigning()->getClientId(),
            'sub' => $sub,
            'nbf' => $now,
            'exp' => $now + $this->jwtSigning()->getExpire(),
        ];

        return JWT::encode($payload, $this->jwtSigning()->getSecretKey(), 'HS256');
    }
}
