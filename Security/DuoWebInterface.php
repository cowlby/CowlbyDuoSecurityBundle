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

interface DuoWebInterface
{
    public function getHost();

    public function signRequest($username);

    public function verifyResponse($sigResponse);
}
