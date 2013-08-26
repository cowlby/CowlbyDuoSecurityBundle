<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class DuoSecurityToken extends AbstractToken
{
    private $sigResponse;
    private $providerKey;

    public function __construct($sigResponse, $providerKey, array $roles = array())
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->sigResponse = $sigResponse;
        $this->providerKey = $providerKey;

        $this->setAuthenticated(count($roles) > 0);
    }

    public function getSigResponse()
    {
        return $this->sigResponse;
    }

    public function getCredentials()
    {
        return null;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }
}
