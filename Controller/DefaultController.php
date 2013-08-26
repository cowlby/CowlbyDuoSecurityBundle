<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Templating\EngineInterface;

class DefaultController extends ContainerAware
{
    private $templating;
    private $router;

    public function __construct(EngineInterface $templating, RouterInterface $router)
    {
        $this->templating = $templating;
        $this->router = $router;
    }

    public function loginAction(Request $request)
    {
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Default:login.html.twig', array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        )));
    }

    public function logoutAction()
    {
    }

    public function loginCheckAction()
    {
    }

    public function duoCheckAction()
    {
    }

    public function duoVerifyAction()
    {
    }
}
