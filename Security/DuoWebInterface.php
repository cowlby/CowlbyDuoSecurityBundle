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

/**
 * @author Jose Prado <cowlby@me.com>
 */
interface DuoWebInterface
{
    const DUO_PREFIX = "TX";
    const APP_PREFIX = "APP";
    const AUTH_PREFIX = "AUTH";

    const DUO_EXPIRE = 300;
    const APP_EXPIRE = 3600;

    public function getHost();

    public function signRequest($username);

    public function verifyResponse($sigResponse);
}
