<?php

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cowlby_duo_security');

        $rootNode
            ->children()
                ->arrayNode('duo')
                    ->isRequired()
                    ->children()
                        ->scalarNode('ikey')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('skey')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('akey')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
