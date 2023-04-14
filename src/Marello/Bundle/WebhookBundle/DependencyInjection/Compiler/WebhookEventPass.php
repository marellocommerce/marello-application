<?php

namespace Marello\Bundle\WebhookBundle\DependencyInjection\Compiler;

use Marello\Bundle\WebhookBundle\EventListener\WebhookListenerInterface;
use Marello\Bundle\WebhookBundle\Provider\WebhookEventProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WebhookEventPass implements CompilerPassInterface
{
    public const TAG = 'marello_webhook.event';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(WebhookEventProvider::class)) {
            return;
        }

        $services = $container->findTaggedServiceIds(self::TAG);
        if (empty($services)) {
            return;
        }

        $registryDefinition = $container->getDefinition(WebhookEventProvider::class);

        foreach ($services as $id => $attributes) {
            $registryDefinition->addMethodCall('addEvent', [new Reference($id)]);
        }
    }
}
