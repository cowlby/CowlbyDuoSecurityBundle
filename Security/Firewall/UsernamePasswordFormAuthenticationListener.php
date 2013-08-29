<?php

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

        if ($this->options['intention'] !== 'duo_first_factor') {
            return $authenticatedToken;
        }

        $request->getSession()->clear(SecurityContextInterface::AUTHENTICATION_ERROR);

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Authentication:duo.html.twig', array(
            'duo_options' => json_encode(array(
                'sig_request' => $this->duo->signRequest($authenticatedToken->getUser()),
                'host' => $this->duo->getHost(),
                'post_action' => $this->httpUtils->generateUri($request, 'cowlby_duo_security_duo_verify')
            ), JSON_UNESCAPED_SLASHES)
        )));
    }
}
