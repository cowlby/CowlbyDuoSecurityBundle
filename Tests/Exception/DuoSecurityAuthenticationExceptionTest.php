<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests;

use Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException;

class DuoSecurityAuthenticationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMessageKey()
    {
        $exception = new DuoSecurityAuthenticationException('message');

        $this->assertEquals('Duo Security authentication unsuccessful.', $exception->getMessageKey());
    }

    /**
     * @expectedException Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException
     * @expectedExceptionMessage dummy message
     */
    public function testThrownException()
    {
        throw new DuoSecurityAuthenticationException('dummy message');
    }
}
