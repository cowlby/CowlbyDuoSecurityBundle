<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests\Security\Firewall;

use Cowlby\Bundle\DuoSecurityBundle\Security\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsernamePasswordFormAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony\Component\EventDispatcher\EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony\Component\HttpFoundation\Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        if (!class_exists('Symfony\Component\HttpKernel\HttpKernel')) {
            $this->markTestSkipped('The "HttpKernel" component is not available');
        }
    }

    public function testHandleWithDuoSecurityOff()
    {
        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $sessionStrategy = $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $providerKey = 'key';
        $successHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
        $failureHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');
        $options = array('duo_security' => false, 'require_previous_session' => false);

        $httpUtils
            ->expects($this->once())
            ->method('checkRequestPath')
            ->will($this->returnValue(true))
        ;

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'))
            ->will($this->returnValue($token))
        ;

        $response = new Response();
        $successHandler
            ->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->will($this->returnValue($response))
        ;

        $listener = new UsernamePasswordFormAuthenticationListener(
            $securityContext,
            $authManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            $options
        );

        $request = new Request(array(), array('_username' => 'username', '_password' => 'password'));
        $request->setMethod('POST');
        $request->setSession($this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface'));

        $event = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $event
            ->expects($this->once())
            ->method('setResponse')
        ;

        $listener->handle($event);
    }

    public function testHandleWithDuoSecurityOn()
    {
        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $sessionStrategy = $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $providerKey = 'key';
        $successHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
        $failureHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');
        $options = array('duo_security' => true, 'require_previous_session' => false);

        $httpUtils
            ->expects($this->once())
            ->method('checkRequestPath')
            ->will($this->returnValue(true))
        ;

        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user))
        ;

        $authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'))
            ->will($this->returnValue($token))
        ;

        $duo = $this->getMock('Cowlby\Bundle\DuoSecurityBundle\Security\DuoWebInterface');
        $duo->expects($this->once())->method('getHost');
        $duo
            ->expects($this->once())
            ->method('signRequest')
            ->will($this->returnValue(''))
        ;

        $templating = $this->getMock('Symfony\Component\Templating\EngineInterface');
        $listener = new UsernamePasswordFormAuthenticationListener(
            $securityContext,
            $authManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            $options
        );

        $listener->setTemplating($templating);
        $listener->setDuo($duo);

        $request = new Request(array(), array('_username' => 'username', '_password' => 'password'));
        $request->setMethod('POST');
        $request->setSession($this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface'));

        $event = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $event
            ->expects($this->once())
            ->method('setResponse')
        ;

        $listener->handle($event);
    }
}
