<?php

namespace Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class WarehouseNotifierRegistryCompilerPass implements CompilerPassInterface
{
    const TAG = 'marello_inventory_warehouse_notifier';
    const SERVICE = 'Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierRegistry';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (empty($taggedServices)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::SERVICE);
        foreach ($taggedServices as $notifier => $value) {
            $registryDefinition->addMethodCall('addNotifier', [new Reference($notifier)]);
        }
    }
}
