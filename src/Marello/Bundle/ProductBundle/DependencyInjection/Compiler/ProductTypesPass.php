<?php

namespace Marello\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProductTypesPass implements CompilerPassInterface
{
    const PROVIDER_SERVICE = 'marello_product.provider.product_types';
    const TAG = 'marello_product.product_type';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::PROVIDER_SERVICE)) {
            return;
        }

        $services = $container->findTaggedServiceIds(self::TAG);
        if (empty($services)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::PROVIDER_SERVICE);

        foreach ($services as $id => $attributes) {
            $registryDefinition->addMethodCall('addProductType', [new Reference($id)]);
        }
    }
}
