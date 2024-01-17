<?php

namespace Marello\Bundle\WebhookBundle\DependencyInjection;

use Marello\Bundle\WebhookBundle\Event\WebhookEventInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MarelloWebhookExtension extends Extension
{
    public const TAG = 'marello_webhook.event';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('services.yml');
        $loader->load('integration.yml');
        $loader->load('form.yml');
        $loader->load('mq_topics.yml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));

        // Add webhook event for each model implements WebhookEventInterface
        // Works only with autoconfigure: true
        $container
            ->registerForAutoconfiguration(WebhookEventInterface::class)
            ->addTag(self::TAG);
    }
}
