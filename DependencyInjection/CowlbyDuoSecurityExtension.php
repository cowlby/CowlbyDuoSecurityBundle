<?php

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CowlbyDuoSecurityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['duo'])) {
            $container->setParameter('cowlby_duo_security.duo.ikey', $config['duo']['ikey']);
            $container->setParameter('cowlby_duo_security.duo.skey', $config['duo']['skey']);
            $container->setParameter('cowlby_duo_security.duo.akey', $config['duo']['akey']);
            $container->setParameter('cowlby_duo_security.duo.host', $config['duo']['host']);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controllers.xml');
    }
}
