<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InventoryLevelsGridCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->removeDefinition('marello_inventory.listener.datagrid.inventory_levels');
    }
}
