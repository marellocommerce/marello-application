<?php

namespace Marello\Bundle\OrderBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OrderItemDataProvidersPass implements CompilerPassInterface
{
    const COMPOSITE_SERVICE = 'marello_order.provider.form_changes.items';
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

        $providers = [];
        foreach ($services as $id => $attributes) {
            if (!array_key_exists('type', $attributes[0])) {
                throw new \InvalidArgumentException(
                    sprintf('Attribute "type" is missing for "%s" tag at "%s" service', self::TAG, $id)
                );
            }
            if (!array_key_exists('priority', $attributes[0])) {
                throw new \InvalidArgumentException(
                    sprintf('Attribute "priority" is missing for "%s" tag at "%s" service', self::TAG, $id)
                );
            }
            $providers[(int)$attributes[0]['priority']][] = ['id' => $id, 'type' => $attributes[0]['type']];
        }
        ksort($providers);
        $providers = call_user_func_array('array_merge', $providers);

        foreach ($providers as $provider) {
            $registryDefinition->addMethodCall('addProvider', [$provider['type'], new Reference($provider['id'])]);
        }
    }
}
