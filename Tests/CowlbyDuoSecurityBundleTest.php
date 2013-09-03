<?php

/*
 * This file is part of the CowlbyDuoSecurityBundle package.
 *
 * (c) Jose Prado <cowlby@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cowlby\Bundle\DuoSecurityBundle\Tests;

use Cowlby\Bundle\DuoSecurityBundle\CowlbyDuoSecurityBundle;

class CowlbyDuoSecurityBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildLoadsSecurityListenerFactories()
    {
        $extension = $this->getMock('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension');
        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder
            ->expects($this->once())
            ->method('getExtension')
            ->with('security')
            ->will($this->returnValue($extension))
        ;

        $duoFactory = 'Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory\DuoSecurityFactory';
        $formFactory = 'Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory';
        $extension
            ->expects($this->at(0))
            ->method('addSecurityListenerFactory')
            ->with($this->logicalOr($this->isInstanceOf($duoFactory), $this->isInstanceOf($formFactory)))
        ;
        $extension
            ->expects($this->at(1))
            ->method('addSecurityListenerFactory')
            ->with($this->logicalOr($this->isInstanceOf($duoFactory), $this->isInstanceOf($formFactory)))
        ;

        $bundle = new CowlbyDuoSecurityBundle();
        $bundle->build($builder);
    }
}
