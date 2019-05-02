<?php

namespace Marello\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EntityNotificationConfigurationProvidersPass implements CompilerPassInterface
{
    const COMPOSITE_SERVICE = 'marello_notification.provider.entity_notification_configuration';
    const TAG = 'marello_entity_notification_configuration_provider';

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
        
        foreach ($services as $id => $attributes) {
            if (!array_key_exists('class', $attributes[0])) {
                throw new \InvalidArgumentException(
                    sprintf('Attribute "class" is missing for "%s" tag at "%s" service', self::TAG, $id)
                );
            }
            $registryDefinition->addMethodCall('addProvider', [$attributes[0]['class'], new Reference($id)]);
        }
    }
}
