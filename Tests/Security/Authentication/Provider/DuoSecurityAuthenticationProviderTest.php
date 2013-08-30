<?php
namespace Cowlby\Bundle\DuoSecurityBundle\Tests\Security\Authentication\Provider;

use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Provider\DuoSecurityAuthenticationProvider;

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

        $mock
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('key'))
        ;

        $mock->setAttributes(array('foo' => 'bar'));

        return $mock;
    }

    protected function getProvider($user = false, $userChecker = false)
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

        $duoWeb = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface', array('getHost', 'signRequest', 'verifyResponse'));
        $duoWeb
            ->expects($this->any())
            ->method('verifyResponse')
            ->will($this->returnValue('username'))
        ;

        return new DuoSecurityAuthenticationProvider($userProvider, $userChecker, 'key', $duoWeb);
    }
}
