<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReplenishmentStrategiesCompilerPass implements CompilerPassInterface
{
    const TAG = 'marello_replenishment_strategy';
    const SERVICE = 'marelloenterprise_replenishment.replenishment_strategy.registry';

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
