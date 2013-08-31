<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests\Security\Authentication\Token;

use Cowlby\Bundle\DuoSecurityBundle\Security\Authentication\Token\DuoSecurityToken;
use Symfony\Component\Security\Core\Role\Role;

class DuoSecurityTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $token = new DuoSecurityToken('foo', 'bar');
        $this->assertFalse($token->isAuthenticated());

        $token = new DuoSecurityToken('foo', 'bar', array('ROLE_FOO'));
        $this->assertEquals(array(new Role('ROLE_FOO')), $token->getRoles());
        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals('bar', $token->getProviderKey());
    }

    public function testSetAuthenticatedToTrue()
    {
        $token = new DuoSecurityToken('foo', 'bar');
        $token->setAuthenticated(true);
    }

    public function testSetAuthenticatedToFalse()
    {
        $token = new DuoSecurityToken('foo', 'bar');
        $token->setAuthenticated(false);
        $this->assertFalse($token->isAuthenticated());
    }

    public function testEraseCredentials()
    {
        $token = new DuoSecurityToken('foo', 'bar');
        $token->eraseCredentials();
        $this->assertEquals('', $token->getCredentials());
    }

    public function testToString()
    {
        $token = new DuoSecurityToken('foo', 'bar', array('A', 'B'));
        $this->assertEquals('DuoSecurityToken(user="", authenticated=true, roles="A, B")', (string) $token);
    }
}
