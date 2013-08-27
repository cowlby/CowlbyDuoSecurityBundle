<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Templating\EngineInterface;

class DuoSecurityFormAuthenticationListener extends AbstractAuthenticationListener
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
        $username = trim($request->request->get($this->options['username_parameter'], null, true));
        $password = $request->request->get($this->options['password_parameter'], null, true);

        $request->getSession()->set(SecurityContextInterface::LAST_USERNAME, $username);

        $authenticatedToken = $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, $this->providerKey));

        $user = $authenticatedToken->getUser();

        if ($user instanceof UserInterface) {
            $user = $user->getUsername();
        }

        $duoOptions = json_encode(array(
            'sig_request' => $this->duo->signRequest($user),
            'host' => $this->duo->getHost(),
            'post_action' => $this->httpUtils->generateUri($request, 'cowlby_duo_security_duo_verify')
        ), JSON_UNESCAPED_SLASHES);

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Default:duo.html.twig', array(
            'duo_options' => $duoOptions
        )));
    }
}
