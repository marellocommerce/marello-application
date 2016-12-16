<?php

namespace Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InventoryBalancerRegistryCompilerPass implements CompilerPassInterface
{
    const INVENTORY_BALANCER_TAG   = 'marello_inventory.manager.balancer.inventory_balancer';
    const REGISTRY_SERVICE_ID   = 'marello_inventory.manager.marello_inventory_balancer_registry';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $validationRules  = $container->findTaggedServiceIds(self::INVENTORY_BALANCER_TAG);

        $registry = $container->findDefinition(self::REGISTRY_SERVICE_ID);

        foreach ($validationRules as $serviceId => $tags) {
            $ref = new Reference($serviceId);
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerInventoryBalancer', [$tag['alias'], $ref]);
            }
        }
    }
}
