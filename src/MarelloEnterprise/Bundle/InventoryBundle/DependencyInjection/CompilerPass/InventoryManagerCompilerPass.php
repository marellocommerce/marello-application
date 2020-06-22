<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use MarelloEnterprise\Bundle\InventoryBundle\Manager\InventoryManager;

class InventoryManagerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('marello_inventory.manager.inventory_manager');
        $definition->setClass(InventoryManager::class);
    }
}
