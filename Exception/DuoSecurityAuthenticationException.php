<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * DuoSecurityAuthenticationException is thrown when Duo Security
 * authentication is unsuccessful.
 *
 * @author Jose Prado <cowlby@me.com>
 */
class DuoSecurityAuthenticationException extends AuthenticationException
{
    /**
     * {@inheritDoc}
     */
    public function getMessageKey()
    {
        return 'Duo Security authentication unsuccessful.';
    }
}
