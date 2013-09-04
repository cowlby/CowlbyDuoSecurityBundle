<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests\Security;

use Cowlby\Bundle\DuoSecurityBundle\Security\DuoWeb;

class DuoWebTest extends \PHPUnit_Framework_TestCase
{
    private static $ikey = 'DIGTF08J47ML1L3B5R5X';
    private static $skey = '6OofAhKYidYZQgAFlXUVyzJ1iwqbtS8yDYzp406E';
    private static $akey = '3ujoyvglcavmusd4rdtm2somylkjsqlgoxrdlndt';
    private static $host = 'api-XXXXXXXX.duosecurity.com';
    private static $sigPattern = '/^TX\|[^\|]+\|[^:]+:APP\|[^\|]+\|[^\|]+$/';

    public function testGetHost()
    {
        $duo = new DuoWeb(self::$ikey, self::$skey, self::$akey, self::$host);
        $this->assertEquals(self::$host, $duo->getHost(), 'Did not return expected host.');
    }

    public function testSignRequestWithUserString()
    {
        $duo = new DuoWeb(self::$ikey, self::$skey, self::$akey, self::$host);

        $user = 'mock';
        $this->assertRegExp(self::$sigPattern, $duo->signRequest($user), 'Did not return valid signature.');
    }

    public function testSignRequestWithUserInterface()
    {
        $duo = new DuoWeb(self::$ikey, self::$skey, self::$akey, self::$host);
        $user = $this->getMock(
            'Symfony\Component\Security\Core\User\UserInterface',
            array(
                'getUsername',
                'getRoles',
                'getSalt',
                'getPassword',
                'eraseCredentials'
            )
        );

        $user
            ->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('mock'))
        ;

        $this->assertRegExp(self::$sigPattern, $duo->signRequest($user), 'Did not return valid signature.');
    }

    public function testVerifyResponse()
    {
        $timestamp = time() - 60 * 60 * 24 * 365 * 10;
        $duo = new DuoWeb(self::$ikey, self::$skey, self::$akey, self::$host, $timestamp);

        $sigResponse = 'AUTH|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNDc0MDI=|dbfd7894ee7b41db6c846e6b609063895565c0e4:APP|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNTA5MzI=|289d9115a30ca3dd62bb9bf9cff4eca2e4284169';
        $this->assertEquals('cowlby', $duo->verifyResponse($sigResponse), 'Username mismatch');
    }

    /**
     * @expectedException Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException
     * @expectedExceptionMessage Invalid Duo Web response.
     */
    public function testVerifyResponseWithTamperedMessage()
    {
        $timestamp = time() - 60 * 60 * 24 * 365 * 10;
        $duo = new DuoWeb('01234567890123456789', self::$skey, self::$akey, self::$host, $timestamp);

        $sigResponse = 'FAKE|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNDc0MDI=|dbfd7894ee7b41db6c846e6b609063895565c0e4:APP|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNTA5MzI=|289d9115a30ca3dd62bb9bf9cff4eca2e4284169';
        $user = $duo->verifyResponse($sigResponse);
    }

    /**
     * @expectedException Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException
     * @expectedExceptionMessage Invalid Duo Web response.
     */
    public function testVerifyResponseWithInvalidPrefix()
    {
        $timestamp = time() - 60 * 60 * 24 * 365 * 10;
        $duo = new DuoWeb('01234567890123456789', self::$skey, self::$akey, self::$host, $timestamp);

        $sigResponse = 'AUTH|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyCDc0MDI=|dbfd7894ee7b41db6c846e6b609063895565c0e4:APP|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNTA5MzI=|289d9115a30ca3dd62bb9bf9cff4eca2e4284169';
        $user = $duo->verifyResponse($sigResponse);
    }

    /**
     * @expectedException Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException
     * @expectedExceptionMessage Can not authenticate from a different integration.
     */
    public function testVerifyResponseWithDifferentIntegrationKey()
    {
        $timestamp = time() - 60 * 60 * 24 * 365 * 10;
        $duo = new DuoWeb('01234567890123456789', self::$skey, self::$akey, self::$host, $timestamp);

        $sigResponse = 'AUTH|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNDc0MDI=|dbfd7894ee7b41db6c846e6b609063895565c0e4:APP|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNTA5MzI=|289d9115a30ca3dd62bb9bf9cff4eca2e4284169';
        $user = $duo->verifyResponse($sigResponse);
    }

    /**
     * @expectedException Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException
     * @expectedExceptionMessage Authentication request expired.
     */
    public function testVerifyResponseAfterExpiration()
    {
        $timestamp = time() + 60 * 60 * 24 * 365 * 10;
        $duo = new DuoWeb(self::$ikey, self::$skey, self::$akey, self::$host, $timestamp);

        $sigResponse = 'AUTH|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNDc0MDI=|dbfd7894ee7b41db6c846e6b609063895565c0e4:APP|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNTA5MzI=|289d9115a30ca3dd62bb9bf9cff4eca2e4284169';
        $user = $duo->verifyResponse($sigResponse);
    }

    /**
     * @expectedException Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException
     * @expectedExceptionMessage Duo Security user mismatch.
     */
    public function testVerifyResponseWithTamperedUser()
    {
        $timestamp = time() - 60 * 60 * 24 * 365 * 10;
        $duo = new DuoWeb(self::$ikey, self::$skey, self::$akey, self::$host, $timestamp);

        $sigResponse = 'AUTH|Y293bGJ5fERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNjMyMjA=|14b1dc969e8c87b7ad56d71b74090e81b6e0b1eb:APP|anByYWRvfERJR1RGMDhKNDdNTDFMM0I1UjVYfDEzNzgyNjY3ODg=|97a549d5ef08c287708d9629bc9770dc57ee7b8d';
        $user = $duo->verifyResponse($sigResponse);
    }
}
