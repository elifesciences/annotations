<?php

namespace eLife\HypothesisClient\Credentials;

final class UserManagementCredentials extends Credentials
{
    public function getAuthorizationBasic() : string
    {
        return 'Basic '.base64_encode($this->getClientId().':'.$this->getClientSecret());
    }
}
