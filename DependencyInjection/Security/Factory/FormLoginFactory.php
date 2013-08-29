<?php

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory as BaseFactory;

class FormLoginFactory extends BaseFactory
{
    public function getKey()
    {
        return 'cowlby_duo_security-form_login';
    }

    protected function getListenerId()
    {
        return 'cowlby_duo_security.security.authentication.listener.form';
    }
}
