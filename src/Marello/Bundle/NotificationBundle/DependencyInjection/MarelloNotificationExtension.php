<?php

namespace Marello\Bundle\NotificationBundle\DependencyInjection;

use Oro\Bundle\NotificationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MarelloNotificationExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $config['settings']['email_notification_sender_name']['value'] = 'Marello';
        $container->prependExtensionConfig('oro_notification', array_intersect_key($config, array_flip(['settings'])));
    }
}
