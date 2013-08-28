<?php
namespace Cowlby\Bundle\DuoSecurityBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

class AuthenticationController extends ContainerAware
{
    private $duo;
    private $templating;
    private $routing;

    public function __construct(DuoWebInterface $duo, EngineInterface $templating, RouterInterface $routing)
    {
        $this->duo = $duo;
        $this->templating = $templating;
        $this->routing = $routing;
    }

    public function duoVerifyAction()
    {
    }

    public function duoTestAction()
    {
        $user = 'cowlby';

        $duoOptions = json_encode(array(
            'sig_request' => $this->duo->signRequest($user),
            'host' => $this->duo->getHost(),
            'post_action' => $this->routing->generate('cowlby_duo_security_duo_verify')
        ), JSON_UNESCAPED_SLASHES);

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Authentication:duo.html.twig', array(
            'duo_options' => $duoOptions
        )));
    }
}
