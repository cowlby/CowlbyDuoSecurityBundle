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

use Symfony\Component\Security\Core\User\UserInterface;

class DuoWeb implements DuoWebInterface
{
    const DUO_PREFIX = "TX";
    const APP_PREFIX = "APP";
    const AUTH_PREFIX = "AUTH";

    const DUO_EXPIRE = 300;
    const APP_EXPIRE = 3600;

    const IKEY_LEN = 20;
    const SKEY_LEN = 40;
    const AKEY_LEN = 40; // if this changes you have to change ERR_AKEY

    const ERR_USER = 'ERR|The username passed to sign_request() is invalid.';
    const ERR_IKEY = 'ERR|The Duo integration key passed to sign_request() is invalid.';
    const ERR_SKEY = 'ERR|The Duo secret key passed to sign_request() is invalid.';
    const ERR_AKEY = "ERR|The application secret key passed to sign_request() must be at least 40 characters.";

    private $ikey;
    private $skey;
    private $akey;
    private $host;

    public function __construct($ikey, $skey, $akey, $host)
    {
        $this->ikey = $ikey;
        $this->skey = $skey;
        $this->akey = $akey;
        $this->host = $host;
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

        if (! isset($user) || strlen($user) == 0) {
            return self::ERR_USER;
        }

        if (! isset($this->ikey) || strlen($this->ikey) != self::IKEY_LEN) {
            return self::ERR_IKEY;
        }

        if (! isset($this->skey) || strlen($this->skey) != self::SKEY_LEN) {
            return self::ERR_SKEY;
        }

        if (! isset($this->akey) || strlen($this->akey) < self::AKEY_LEN) {
            return self::ERR_AKEY;
        }

        $vals = $user . '|' . $this->ikey;

        $duo_sig = $this->signVals($this->skey, $vals, self::DUO_PREFIX, self::DUO_EXPIRE);
        $app_sig = $this->signVals($this->akey, $vals, self::APP_PREFIX, self::APP_EXPIRE);

        return $duo_sig . ':' . $app_sig;
    }

    public function verifyResponse($sigResponse)
    {
        list ($auth_sig, $app_sig) = explode(':', $sigResponse);

        $auth_user = $this->parseVals($this->skey, $auth_sig, self::AUTH_PREFIX);
        $app_user = $this->parseVals($this->akey, $app_sig, self::APP_PREFIX);

        if ($auth_user !== $app_user) {
            return null;
        }

        return $auth_user;
    }

    protected function signVals($key, $vals, $prefix, $expire)
    {
        $exp = time() + $expire;

        $val = $vals . '|' . $exp;
        $b64 = base64_encode($val);
        $cookie = $prefix . '|' . $b64;

        $sig = hash_hmac("sha1", $cookie, $key);
        return $cookie . '|' . $sig;
    }

    protected function parseVals($key, $val, $prefix)
    {
        $ts = time();
        list ($u_prefix, $u_b64, $u_sig) = explode('|', $val);

        $sig = hash_hmac("sha1", $u_prefix . '|' . $u_b64, $key);
        if (hash_hmac("sha1", $sig, $key) != hash_hmac("sha1", $u_sig, $key)) {
            return null;
        }

        if ($u_prefix != $prefix) {
            return null;
        }

        list ($user, $ikey, $exp) = explode('|', base64_decode($u_b64));

        if ($ts >= intval($exp)) {
            return null;
        }

        return $user;
    }
}
