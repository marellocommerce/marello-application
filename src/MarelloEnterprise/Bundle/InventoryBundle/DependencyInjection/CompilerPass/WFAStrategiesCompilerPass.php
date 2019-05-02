<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WFAStrategiesCompilerPass implements CompilerPassInterface
{
    const TAG = 'marello_inventory_wfa_strategy';
    const SERVICE = 'marelloenterprise_inventory.wfa_strategy.registry';

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

        foreach ($taggedServices as $strategy => $value) {
            $registryDefinition->addMethodCall('addStrategy', [new Reference($strategy)]);
        }
    }
}
