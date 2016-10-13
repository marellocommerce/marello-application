<?php

namespace Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ShippingServiceRegistryCompilerPass implements CompilerPassInterface
{
    const SHIPPING_INTEGRATION_TAG  = 'marello.shipping.integration';
    const SHIPPING_DATA_FACTORY_TAG = 'marello.shipping.data_factory';
    const SHIPPING_DATA_PROVIDER_TAG = 'marello.shipping.data_provider';

    const REGISTRY_SERVICE_ID = 'marello_shipping.integration.shipping_service_registry';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $integrations  = $container->findTaggedServiceIds(self::SHIPPING_INTEGRATION_TAG);
        $dataFactories = $container->findTaggedServiceIds(self::SHIPPING_DATA_FACTORY_TAG);
        $dataProviders = $container->findTaggedServiceIds(self::SHIPPING_DATA_PROVIDER_TAG);

        $registry = $container->findDefinition(self::REGISTRY_SERVICE_ID);

        foreach ($integrations as $integration => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerIntegration', [$tag['alias'], $integration]);
            }
        }

        foreach ($dataFactories as $factory => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerDataFactory', [$tag['alias'], $factory]);
            }
        }
        
        foreach ($dataProviders as $provider => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerDataProvider', [$tag['class'], $provider]);
            }
        }
    }
}
