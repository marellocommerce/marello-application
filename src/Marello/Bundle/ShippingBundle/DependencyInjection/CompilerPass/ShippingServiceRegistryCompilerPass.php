<?php

namespace Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        if (!$container->hasDefinition(static::REGISTRY_SERVICE_ID)) {
            return;
        }

        $integrations  = $container->findTaggedServiceIds(self::SHIPPING_INTEGRATION_TAG);
        $dataFactories = $container->findTaggedServiceIds(self::SHIPPING_DATA_FACTORY_TAG);
        $dataProviders = $container->findTaggedServiceIds(self::SHIPPING_DATA_PROVIDER_TAG);
        if (empty($integrations) || empty($dataFactories) || empty($dataProviders)) {
            return;
        }

        $registry = $container->getDefinition(self::REGISTRY_SERVICE_ID);

        foreach ($integrations as $integration => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerIntegration', [$tag['alias'], new Reference($integration)]);
            }
        }

        foreach ($dataFactories as $factory => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerDataFactory', [$tag['alias'], new Reference($factory)]);
            }
        }
        
        foreach ($dataProviders as $provider => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerDataProvider', [$tag['class'], new Reference($provider)]);
            }
        }
    }
}
