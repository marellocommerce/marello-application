<?php

namespace Marello\Bundle\InventoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\InventoryBalancerRegistryCompilerPass;

class MarelloInventoryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new InventoryBalancerRegistryCompilerPass());
    }
}
