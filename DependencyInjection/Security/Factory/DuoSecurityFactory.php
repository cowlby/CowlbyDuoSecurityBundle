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

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DuoSecurityFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('check_path', 'cowlby_duo_security_duo_verify');
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'cowlby_duo_security-login';
    }

    protected function getListenerId()
    {
        return 'cowlby_duo_security.security.authentication.listener.duo';
    }

    protected function createAuthProvider(ContainerBuilder $container, $firewallId, $config, $userProviderId)
    {
        $provider = 'cowlby_duo_security.security.authentication.provider.duo.'.$firewallId;
        $container
            ->setDefinition($provider, new DefinitionDecorator('cowlby_duo_security.security.authentication.provider.duo'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $firewallId)
        ;

        return $provider;
    }
}
