<?php

namespace Marello\Bundle\PricingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SubtotalProviderPass implements CompilerPassInterface
{
    const COMPOSITE_SERVICE = 'marello_productprice.pricing.subtotal_provider.composite';
    const TAG = 'marello_pricing.subtotal_provider';
    const PRIORITY = 'priority';
    
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::COMPOSITE_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        if (empty($taggedServices)) {
            return;
        }

        $providers      = [];
        foreach ($taggedServices as $serviceId => $tags) {
            $priority = isset($tags[0][self::PRIORITY]) ? $tags[0][self::PRIORITY] : 0;
            $providers[$priority][] = $serviceId;
        }

        ksort($providers);
        $providers = call_user_func_array('array_merge', $providers);

        $registryDefinition = $container->getDefinition(self::COMPOSITE_SERVICE);

        foreach ($providers as $provider) {
            $registryDefinition->addMethodCall('addProvider', [new Reference($provider)]);
        }
    }
}
