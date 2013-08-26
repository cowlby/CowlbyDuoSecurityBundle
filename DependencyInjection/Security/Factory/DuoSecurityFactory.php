<?php

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;

class DuoSecurityFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('sig_response_parameter', 'sig_response');
    }

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
        return 'cowlby_duo_security.security.authentication.listener.duo_security';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'cowlby_duo_security.security.authentication.provider.duo_security.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('cowlby_duo_security.security.authentication.provider.duo_security'))
            ->replaceArgument(1, new Reference($userProviderId))
            ->replaceArgument(3, $id)
        ;

        return $provider;
    }
}
