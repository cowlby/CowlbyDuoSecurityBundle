<?php
namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Templating\EngineInterface;

class DuoSecurityAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $duo;
    private $templating;
    private $httpUtils;
    private $options;

    /**
     * Constructor.
     */
    public function __construct(DuoWebInterface $duo, EngineInterface $templating, HttpUtils $httpUtils)
    {
        $this->duo = $duo;
        $this->templating = $templating;
        $this->httpUtils = $httpUtils;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $username = $token->getUser()->getUsername();

        $duoOptions = json_encode(array(
            'sig_request' => $this->duo->signRequest($username),
            'host' => $this->duo->getHost(),
            'post_action' => $this->httpUtils->generateUri($request, 'cowlby_duo_security_duo_verify')
        ), JSON_UNESCAPED_SLASHES);

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Default:duo.html.twig', array(
            'duo_options' => $duoOptions
        )));
    }
}
