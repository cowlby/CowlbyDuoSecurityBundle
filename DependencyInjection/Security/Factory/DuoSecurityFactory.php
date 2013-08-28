<?php

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;

class DuoSecurityFactory extends AbstractFactory
{
    public function getPosition()
    {
        return 'http';
    }

    public function getKey()
    {
        return 'cowlby_duo_security-login';
    }

    protected function getListenerId()
    {
        return 'cowlby_duo_security.security.authentication.listener.duo';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'cowlby_duo_security.security.authentication.provider.duo.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('cowlby_duo_security.security.authentication.provider.duo'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $id)
        ;

        return $provider;
    }
}
