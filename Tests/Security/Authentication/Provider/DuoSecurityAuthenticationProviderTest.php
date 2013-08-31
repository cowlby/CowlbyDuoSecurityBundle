<?php

namespace Cowlby\Bundle\DuoSecurityBundle\Tests\Security\Authentication\Provider;

use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Provider\DuoSecurityAuthenticationProvider;
use Symfony\Component\Security\Core\Role\Role;
use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken;

class DuoSecurityAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $provider = $this->getProvider();

        $this->assertTrue($provider->supports($this->getSupportedToken()));
        $this->assertFalse($provider->supports($this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')));
    }

    public function testAuthenticateWhenTokenIsNotSupported()
    {
        $provider = $this->getProvider();

        $this->assertNull($provider->authenticate($this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')));
    }

    /**
     * @expectedException \Cowlby\Bundle\DuoSecurityBundle\Security\Exception\DuoSecurityException
     */
    public function testAuthenticateWhenDuoVeriyFails()
    {
        $userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userChecker = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
        $duoWeb = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface', array('getHost', 'signRequest', 'verifyResponse'));
        $duoWeb
            ->expects($this->any())
            ->method('verifyResponse')
            ->will($this->returnValue(null))
        ;

        $provider = new DuoSecurityAuthenticationProvider($userProvider, $userChecker, 'key', $duoWeb);
        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testAuthenticateWhenUsernameIsNotFound()
    {
        $userProvider = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('guineapig')
            ->will($this->throwException($this->getMock('Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', null, array(), '', false)))
        ;

        $duoWeb = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface', array('getHost', 'signRequest', 'verifyResponse'));
        $duoWeb
            ->expects($this->any())
            ->method('verifyResponse')
            ->will($this->returnValue('guineapig'))
        ;

        $provider = new DuoSecurityAuthenticationProvider($userProvider, $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserCheckerInterface'), 'key', $duoWeb);
        $provider->authenticate($this->getSupportedToken());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccountExpiredException
     */
    public function testAuthenticateWhenPostChecksFails()
    {
        $duoWeb = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface');
        $duoWeb
            ->expects($this->any())
            ->method('verifyResponse')
            ->will($this->returnValue('guineapig'))
        ;

        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('guineapig')
            ->will($this->returnValue($user))
        ;

        $userChecker = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
        $userChecker
            ->expects($this->once())
            ->method('checkPostAuth')
            ->will($this->throwException($this->getMock('Symfony\Component\Security\Core\Exception\AccountExpiredException', null, array(), '', false)))
        ;

        $provider = new DuoSecurityAuthenticationProvider($userProvider, $userChecker, 'key', $duoWeb);
        $provider->authenticate($this->getSupportedToken());
    }

    public function testAuthenticate()
    {
        $duoWeb = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface');
        $duoWeb
            ->expects($this->once())
            ->method('verifyResponse')
            ->will($this->returnValue('guineapig'))
        ;

        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array('ROLE_FOO')))
        ;

        $userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('guineapig')
            ->will($this->returnValue($user))
        ;

        $userChecker = $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');

        $token = new DuoSecurityToken('foo', 'key');
        $token->setAttributes(array('foo' => 'bar'));

        $provider = new DuoSecurityAuthenticationProvider($userProvider, $userChecker, 'key', $duoWeb);
        $authToken = $provider->authenticate($token);

        $this->assertInstanceOf('Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken', $authToken);
        $this->assertSame($user, $authToken->getUser());
        $this->assertEquals(array(new Role('ROLE_FOO')), $authToken->getRoles());
        $this->assertEquals('foo', $authToken->getCredentials());
        $this->assertEquals(array('foo' => 'bar'), $authToken->getAttributes(), '->authenticate() copies token attributes');
    }


    protected function getSupportedToken($user = false, $credentials = false)
    {
        $mock = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken', array('getUser', 'getCredentials', 'getProviderKey'), array(), '', false);

        if (false !== $user) {
            $mock
                ->expects($this->once())
                ->method('getUser')
                ->will($this->returnValue($user))
            ;
        }

        if (false !== $credentials) {
            $mock
                ->expects($this->once())
                ->method('getCredentials')
                ->will($this->returnValue($credentials))
            ;
        }

        $mock
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('key'))
        ;

        $mock->setAttributes(array('foo' => 'bar'));

        return $mock;
    }

    protected function getProvider($user = false, $userChecker = false, $duoResponse = false)
    {
        $userProvider = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface');
        if (false !== $user) {
            $userProvider
                ->expects($this->once())
                ->method('loadUserByUsername')
                ->will($this->returnValue($user))
            ;
        }

        if (false === $userChecker) {
            $userChecker = $this->getMock('Symfony\\Component\\Security\\Core\\User\\UserCheckerInterface');
        }

        if (false === $duoResponse) {
            $duoResponse = $user;
        }

        $duoWeb = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface', array('getHost', 'signRequest', 'verifyResponse'));
        $duoWeb
            ->expects($this->any())
            ->method('verifyResponse')
            ->will($this->returnValue($duoResponse))
        ;

        return new DuoSecurityAuthenticationProvider($userProvider, $userChecker, 'key', $duoWeb);
    }
}
