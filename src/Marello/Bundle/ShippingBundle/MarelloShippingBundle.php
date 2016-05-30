<?php

namespace Marello\Bundle\ShippingBundle;

use Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\ShippingServiceRegistryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloShippingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ShippingServiceRegistryCompilerPass());
    }
}
