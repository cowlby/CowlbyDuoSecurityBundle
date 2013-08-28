<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class DuoSecurityToken extends AbstractToken
{
    private $credentials;
    private $providerKey;

    public function __construct($credentials, $providerKey, array $roles = array())
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->credentials = $credentials;
        $this->providerKey = $providerKey;

        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function eraseCredentials()
    {
        parent::eraseCredentials();
        $this->credentials = null;
    }
}
