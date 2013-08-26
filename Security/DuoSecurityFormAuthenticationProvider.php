<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DuoSecurityFormAuthenticationProvider extends DaoAuthenticationProvider
{
    public function authenticate(TokenInterface $token)
    {
        $result = parent::authenticate($token);
        return false;
    }
}
