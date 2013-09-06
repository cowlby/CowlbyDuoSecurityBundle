<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Controller;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Provides authentication controller actions.
 *
 * @author Jose Prado <cowlby@me.com>
 */
class AuthenticationController
{
    private $templating;
    private $csrfProvider;

    /**
     * Constructor.
     *
     * @param EngineInterface       $templating   A templating engine.
     * @param CsrfProviderInterface $csrfProvider An optional CSRF Provider.
     */
    public function __construct(EngineInterface $templating, CsrfProviderInterface $csrfProvider = null)
    {
        $this->templating = $templating;
        $this->csrfProvider = $csrfProvider;
    }

    /**
     * Renders the login template.
     *
     * @param Request $request The request.
     *
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR, null);
        $lastUsername = $session->get(SecurityContextInterface::LAST_USERNAME, null);
        $csrfToken = isset($this->csrfProvider) ? $this->csrfProvider->generateCsrfToken('authenticate') : null;

        $content = $this->templating->render(
            'CowlbyDuoSecurityBundle:Authentication:login.html.twig',
            array(
                'last_username' => $lastUsername,
                'csrf_token' => $csrfToken,
                'error' => $error,
            )
        );

        return new Response($content);
    }
}
