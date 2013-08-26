<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Cowlby\Bundle\DuoSecurityBundle\Security\Exception\DuoSecurityException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DuoSecurityAuthenticationProvider implements AuthenticationProviderInterface
{
    private $duo;
    private $userProvider;
    private $userChecker;
    private $providerKey;

    /**
     * Constructor.
     *
     * @param DuoWebInterface       $duo          A DuoWebInterface instance
     * @param UserProviderInterface $userProvider An UserProviderInterface instance
     * @param UserCheckerInterface  $userChecker  An UserCheckerInterface instance
     * @param string                $providerKey  The provider key
     */
    public function __construct(DuoWebInterface $duo, UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey)
    {
        $this->duo = $duo;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $username = $this->duo->verifyResponse($token->getSigResponse());

        if (null === $username) {
            throw new DuoSecurityException('Duo Security authentication failure');
        }

        $user = $this->userProvider->loadUserByUsername($username);
        $this->userChecker->checkPostAuth($user);

        $authenticatedToken = new DuoSecurityToken($token->getSigResponse(), $this->providerKey, $user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof DuoSecurityToken && $this->providerKey === $token->getProviderKey();
    }
}
