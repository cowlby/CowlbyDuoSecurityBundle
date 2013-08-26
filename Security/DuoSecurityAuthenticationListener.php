<?php
namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class DuoSecurityAuthenticationListener extends AbstractAuthenticationListener
{
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
