<?php

namespace Cowlby\Bundle\DuoSecurityBundle;

use Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory\DuoSecurityFactory;
use Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 * @author Jose Prado <cowlby@me.com>
 */
class CowlbyDuoSecurityBundle extends Bundle
{
    /**
     * Builds the bundle and registers the two security listener factories
     * needed for Duo Security authentication.
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new DuoSecurityFactory());
        $extension->addSecurityListenerFactory(new FormLoginFactory());
    }
}
