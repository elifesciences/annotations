<?php

namespace eLife\HypothesisClient\Credentials;

class UserManagementCredentials extends Credentials
{
    public function getAuthorizationBasic() : string
    {
        return 'Basic '.base64_encode($this->getClientId().':'.$this->getSecretKey());
    }
}
