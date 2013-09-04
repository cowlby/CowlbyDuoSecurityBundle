<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Security;

use Cowlby\Bundle\DuoSecurityBundle\Exception\DuoSecurityAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

class DuoWeb implements DuoWebInterface
{
    private $ikey;
    private $skey;
    private $akey;
    private $host;
    private $timestamp;

    public function __construct($ikey, $skey, $akey, $host, $timestamp = null)
    {
        $this->ikey = $ikey;
        $this->skey = $skey;
        $this->akey = $akey;
        $this->host = $host;
        $this->timestamp = empty($timestamp) ? time() : $timestamp;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function signRequest($user)
    {
        if ($user instanceof UserInterface) {
            $user = $user->getUsername();
        }

        $data = sprintf('%s|%s', $user, $this->ikey);
        $duoSig = $this->signData($this->skey, $data, self::DUO_PREFIX, self::DUO_EXPIRE);
        $appSig = $this->signData($this->akey, $data, self::APP_PREFIX, self::APP_EXPIRE);

        return sprintf('%s|%s', $duoSig, $appSig);
    }

    public function verifyResponse($sigResponse)
    {
        list($authSig, $appSig) = explode(':', $sigResponse);

        $authUser = $this->decodeSignedData($this->skey, $authSig, self::AUTH_PREFIX);
        $appUser = $this->decodeSignedData($this->akey, $appSig, self::APP_PREFIX);

        if ($authUser !== $appUser) {
            throw new DuoSecurityAuthenticationException('Duo Web users did not match.');
        }

        return $authUser;
    }

    private function hash($key, $message)
    {
        return hash_hmac('sha1', $message, $key);
    }

    private function signData($key, $data, $prefix, $expire)
    {
        $expiration = $this->timestamp + $expire;
        $encodedData = base64_encode(sprintf('%s|%s', $data, $expiration));
        $message = sprintf('%s|%s', $prefix, $encodedData);

        $digest = $this->hash($key, $message);

        return sprintf('%s|%s', $message, $digest);
    }

    private function decodeSignedData($key, $signedData, $expectedPrefix)
    {
        list($sigPrefix, $encodedData, $sigDigest) = explode('|', $signedData);

        $digest = $this->hash($key, sprintf('%s|%s', $sigPrefix, $encodedData));

        if ($this->hash($key, $digest) !== $this->hash($key, $sigDigest) || $sigPrefix !== $expectedPrefix) {
            throw new DuoSecurityAuthenticationException('Invalid Duo Web response.');
        }

        list($user, $ikey, $expiration) = explode('|', base64_decode($encodedData));

        if ($ikey !== $this->ikey) {
            throw new DuoSecurityAuthenticationException('Can not authenticate from a different integration.');
        }

        if ($this->timestamp > $expiration) {
            throw new DuoSecurityAuthenticationException('Authentication request expired.');
        }

        return $user;
    }
}
