<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken;

class DuoSecurityAuthenticationListener extends AbstractAuthenticationListener
{
    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request)
    {
        return $request->request->has('sig_response');
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $sigResponse = $request->request->get('sig_response');
        return $this->authenticationManager->authenticate(new DuoSecurityToken($sigResponse, $this->providerKey));
    }
}
