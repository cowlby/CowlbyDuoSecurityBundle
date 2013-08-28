<?php

namespace Cowlby\Bundle\DuoSecurityBundle;

use Cowlby\Bundle\DuoSecurityBundle\DependencyInjection\Security\Factory\DuoSecurityFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CowlbyDuoSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new DuoSecurityFactory());
    }
}
