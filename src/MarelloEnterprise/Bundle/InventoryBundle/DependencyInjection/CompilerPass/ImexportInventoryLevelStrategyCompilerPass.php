<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use MarelloEnterprise\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy;

class ImexportInventoryLevelStrategyCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('marello_inventory.importexport.strategy.inventory.add_or_replace');
        $definition->setClass(InventoryLevelUpdateStrategy::class);
    }
}
