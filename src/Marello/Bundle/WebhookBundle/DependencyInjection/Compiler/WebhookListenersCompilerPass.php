<?php

namespace Marello\Bundle\WebhookBundle\DependencyInjection\Compiler;

use Marello\Bundle\WebhookBundle\EventListener\WebhookListenerInterface;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WebhookListenersCompilerPass implements CompilerPassInterface
{
    public const KERNEL_LISTENER_TAG   = 'kernel.event_listener';
    public const KERNEL_SUBSCRIBER_TAG = 'kernel.event_subscriber';

    public const DOCTRINE_ORM_LISTENER_TAG = 'doctrine.orm.entity_listener';
    public const DOCTRINE_LISTENER_TAG   = 'doctrine.event_listener';
    public const DOCTRINE_SUBSCRIBER_TAG = 'doctrine.event_subscriber';

    public const WEBHOOK_LISTENER_MANAGER = 'marello_webhook.webhook_listeners.manager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $listeners = array_keys(
            array_merge(
                $container->findTaggedServiceIds(self::KERNEL_LISTENER_TAG),
                $container->findTaggedServiceIds(self::KERNEL_SUBSCRIBER_TAG),
                $container->findTaggedServiceIds(self::DOCTRINE_ORM_LISTENER_TAG),
                $container->findTaggedServiceIds(self::DOCTRINE_LISTENER_TAG),
                $container->findTaggedServiceIds(self::DOCTRINE_SUBSCRIBER_TAG)
            )
        );

        $webhookListeners = [];
        $parameterBag = $container->getParameterBag();
        foreach ($listeners as $listener) {
            $listenerDefinition = $container->getDefinition($listener);
            $className = $parameterBag->resolveValue($listenerDefinition->getClass());

            if (\is_subclass_of($className, WebhookListenerInterface::class)) {
                $webhookListeners[] = $listener;
                $listenerDefinition->setPublic(true);

                foreach ($listenerDefinition->getTags() as $name => $tags) {
                    foreach ($tags as $tag) {
                        $attributes = ['event' => $tag['event'], 'method' => 'triggerWebhookNotificationEvent'];
                        $listenerDefinition->addTag($name, $attributes);
                    }
                }
                //set event-dispatch
                $listenerDefinition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
            }
        }

        //manager
        $definition = $container->getDefinition(self::WEBHOOK_LISTENER_MANAGER);
        $definition->replaceArgument(0, $webhookListeners);
    }
}
