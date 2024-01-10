<?php

namespace Marello\Bundle\WebhookBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const DEFAULT_NOTIFICATION_REDELIVERY = 2;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('marello_webhook');
        $rootNode = $treeBuilder->getRootNode();
        SettingsBuilder::append($rootNode, [
            'notification_redelivery' => [
                'value' => self::DEFAULT_NOTIFICATION_REDELIVERY,
                'type' => 'text'
            ]
        ]);

        return $treeBuilder;
    }
}
