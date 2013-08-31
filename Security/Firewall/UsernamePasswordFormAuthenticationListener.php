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

use Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener as BaseAuthenticationListener;
use Symfony\Component\Templating\EngineInterface;

class UsernamePasswordFormAuthenticationListener extends BaseAuthenticationListener
{
    private $duo;
    private $templating;

    public function setDuo(DuoWebInterface $duo)
    {
        $this->duo = $duo;
    }

    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $authenticatedToken = parent::attemptAuthentication($request);

        if (!$this->options['duo_security']) {
            return $authenticatedToken;
        }

        if ($this->options['post_only']) {
            $rememberMe = $request->request->get('_remember_me', false);
        } else {
            $rememberMe = $request->get('_remember_me', false);
        }

        $request->getSession()->clear(SecurityContextInterface::AUTHENTICATION_ERROR);

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Authentication:duo.html.twig', array(
            'remember_me' => $rememberMe,
            'duo_options' => json_encode(array(
                'sig_request' => $this->duo->signRequest($authenticatedToken->getUser()),
                'host' => $this->duo->getHost(),
                'post_action' => $this->httpUtils->generateUri($request, 'cowlby_duo_security_duo_verify')
            ))
        )));
    }
}
