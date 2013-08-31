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

use Cowlby\Bundle\DuoSecurityBundle\Security\Firewall\DuoSecurityAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DuoSecurityAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
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

    public function testHandleWithValidDuoResponseParameter()
    {
        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $sessionStrategy = $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $providerKey = 'key';
        $successHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
        $failureHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken'))
            ->will($this->returnValue($token))
        ;

        $response = new Response();
        $successHandler
            ->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->will($this->returnValue($response))
        ;

        $listener = new DuoSecurityAuthenticationListener(
            $securityContext,
            $authManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            array('require_previous_session' => false)
        );

        $request = new Request(array(), array('sig_response' => 'duo_response'));
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

    public function testHandleWithNoDuoResponseParameter()
    {
        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $sessionStrategy = $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $providerKey = 'key';
        $successHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
        $failureHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');

        $listener = new DuoSecurityAuthenticationListener(
            $securityContext,
            $authManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            array('require_previous_session' => false)
        );

        $request = new Request();
        $request->setSession($this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface'));
        $event = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', array(), array(), '', false);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $listener->handle($event);
    }

    public function testHandleWhenAuthenticationFails()
    {
        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $sessionStrategy = $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $httpUtils = $this->getMock('Symfony\Component\Security\Http\HttpUtils');
        $providerKey = 'key';
        $successHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
        $failureHandler = $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');

        $authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->isInstanceOf('Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken'))
            ->will($this->throwException(new AuthenticationException()))
        ;

        $response = new Response();
        $failureHandler
            ->expects($this->once())
            ->method('onAuthenticationFailure')
            ->will($this->returnValue($response))
        ;

        $listener = new DuoSecurityAuthenticationListener(
            $securityContext,
            $authManager,
            $sessionStrategy,
            $httpUtils,
            $providerKey,
            $successHandler,
            $failureHandler,
            array('require_previous_session' => false)
        );

        $request = new Request(array(), array('sig_response' => 'duo_response'));
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
