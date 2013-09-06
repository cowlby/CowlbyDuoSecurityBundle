<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory as BaseFactory;

/**
 * FormLoginFactory creates services for form login authentication with Duo
 * Security capabilities.
 *
 * @author Jose Prado <cowlby@me.com>
 */
class FormLoginFactory extends BaseFactory
{
    public function __construct()
    {
        parent::__construct();
        $this->addOption('duo_security', true);
        $this->addOption('login_path', 'cowlby_duo_security_login');
        $this->addOption('check_path', 'cowlby_duo_security_duo_check');
    }

    public function getKey()
    {
        return 'cowlby_duo_security-form_login';
    }

    protected function getListenerId()
    {
        return 'cowlby_duo_security.security.authentication.listener.form';
    }
}
