<?php

namespace Marello\Bundle\InventoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WFAStrategiesCompilerPass;
use Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\BalancerStrategiesCompilerPass;
use Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WarehouseNotifierRegistryCompilerPass;

class MarelloInventoryBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BalancerStrategiesCompilerPass());
        $container->addCompilerPass(new WFAStrategiesCompilerPass());
        $container->addCompilerPass(new WarehouseNotifierRegistryCompilerPass());

        parent::build($container);
    }
}
