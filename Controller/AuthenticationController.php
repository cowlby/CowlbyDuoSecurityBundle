<?php
namespace Cowlby\Bundle\DuoSecurityBundle\Controller;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;

class AuthenticationController
{
    private $templating;
    private $csrfProvider;

    public function __construct(EngineInterface $templating, CsrfProviderInterface $csrfProvider = null)
    {
        $this->templating = $templating;
        $this->csrfProvider = $csrfProvider;
    }

    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR, null);
        } else {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR, null);
        }

        $lastUsername = $session->get(SecurityContextInterface::LAST_USERNAME, null);
        $csrfToken = isset($this->csrfProvider) ? $this->csrfProvider->generateCsrfToken('authenticate') : null;

        return new Response($this->templating->render('CowlbyDuoSecurityBundle:Authentication:login.html.twig', array(
            'last_username' => $lastUsername,
            'csrf_token' => $csrfToken,
            'error' => $error,
        )));
    }
}
