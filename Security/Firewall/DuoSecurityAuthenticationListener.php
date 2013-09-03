<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Security\Firewall;

use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

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
