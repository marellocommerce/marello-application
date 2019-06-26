<?php

namespace Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AdditionalPlaceholderProviderPass implements CompilerPassInterface
{
    const PROVIDER_SERVICE = 'marello_core.provider.additional_placeholder_provider';
    const TAG = 'marello_core.additional_placeholder_data';

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
            $registryDefinition->addMethodCall('addAdditionalPlaceholderDataProvider', [new Reference($id)]);
        }
    }
}
