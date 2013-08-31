<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests\DependencyInjection;

use Cowlby\Bundle\DuoSecurityBundle\CowlbyDuoSecurityBundle;
use Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\CowlbyDuoSecurityExtension;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class CowlbyDuoSecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $container = $this->getContainer('container1');

        $this->assertTrue($container->hasDefinition('cowlby_duo_security.duo_web'));
        $this->assertTrue($container->hasDefinition('cowlby_duo_security.security.authentication.provider.duo'));
        $this->assertTrue($container->hasDefinition('cowlby_duo_security.security.authentication.listener.duo'));
        $this->assertTrue($container->hasDefinition('cowlby_duo_security.security.authentication.listener.form'));
        $this->assertTrue($container->hasDefinition('cowlby_duo_security.controller.authentication'));
    }

    protected function getContainer($file)
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new CowlbyDuoSecurityExtension());

        $bundle = new CowlbyDuoSecurityBundle();
        $bundle->build($container); // Attach all default factories

        $loadXml = new YamlFileLoader($container, new FileLocator(__DIR__.'/Fixtures/yml'));
        $loadXml->load($file.'.yml');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
