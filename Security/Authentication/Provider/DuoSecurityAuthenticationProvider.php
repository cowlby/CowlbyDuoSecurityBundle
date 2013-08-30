<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Provider;

use Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface;
use Cowlby\Bundle\DuoSecurityBundle\Security\Exception\DuoSecurityException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken;

class DuoSecurityAuthenticationProvider implements AuthenticationProviderInterface
{
    private $duo;
    private $userProvider;
    private $userChecker;
    private $providerKey;

    /**
     * Constructor.
     *
     * @param UserProviderInterface $userProvider An UserProviderInterface instance
     * @param UserCheckerInterface  $userChecker  An UserCheckerInterface instance
     * @param string                $providerKey  The provider key
     * @param DuoWebInterface       $duo          A DuoWebInterface instance
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, DuoWebInterface $duo)
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->duo = $duo;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $username = $this->duo->verifyResponse($token->getCredentials());

        if (null === $username) {
            throw new DuoSecurityException('Duo Security authentication failure');
        }

        $user = $this->userProvider->loadUserByUsername($username);
        $this->userChecker->checkPostAuth($user);

        $authenticatedToken = new DuoSecurityToken($token->getCredentials(), $this->providerKey, $user->getRoles());
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
