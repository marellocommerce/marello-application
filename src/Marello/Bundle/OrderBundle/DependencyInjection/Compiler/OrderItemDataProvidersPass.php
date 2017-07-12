<?php

namespace Marello\Bundle\OrderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OrderItemDataProvidersPass implements CompilerPassInterface
{
    const COMPOSITE_SERVICE = 'marello_order.provider.order_item_data.composite';
    const TAG = 'marello_order.order_item_data_provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::COMPOSITE_SERVICE)) {
            return;
        }

        $services = $container->findTaggedServiceIds(self::TAG);
        if (empty($services)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::COMPOSITE_SERVICE);

        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!array_key_exists('type', $attributes)) {
                    throw new \InvalidArgumentException(
                        sprintf('Attribute "type" is missing for "%s" tag at "%s" service', self::TAG, $id)
                    );
                }

                $reference = new Reference($id);
                $registryDefinition->addMethodCall('addProvider', [$attributes['type'], $reference]);
            }
        }
    }
}
