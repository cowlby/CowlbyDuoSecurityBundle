<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Contains configuration structure for the bundle to house the Duo
 * Security integration data.
 *
 * @author Jose Prado <colwby@me.com>
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
