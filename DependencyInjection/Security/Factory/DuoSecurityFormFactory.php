<?php

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class DuoSecurityFormFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getKey()
    {
        return 'cowlby_duo_security-form-login';
    }

    protected function getListenerId()
    {
        return 'cowlby_duo_security.security.authentication.listener.duo_security_form';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'cowlby_duo_security.security.authentication.provider.duo_security_form.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('cowlby_duo_security.security.authentication.provider.duo_security_form'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $id)
        ;

        return $provider;
    }

    protected function createAuthenticationSuccessHandler($container, $id, $config)
    {
        $successHandlerId = 'cowlby_duo_security.security.authentication.success_handler.'.$id.'.'.str_replace('-', '_', $this->getKey());

        $successHandler = $container->setDefinition($successHandlerId, new DefinitionDecorator('cowlby_duo_security.security.authentication.success_handler'));

        return $successHandlerId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'security.authentication.form_entry_point.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('security.authentication.form_entry_point'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config['login_path'])
            ->addArgument($config['use_forward'])
        ;

        return $entryPointId;
    }
}
