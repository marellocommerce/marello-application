<?php

namespace Marello\Bundle\NotificationMessageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    public const SYSTEM_CONFIG_PATH_ASSIGNED_GROUPS = 'marello_notification_message.assigned_groups';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(MarelloNotificationMessageExtension::ALIAS);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                'assigned_groups' => [
                    'value' => []
                ],
            ]
        );

        return $treeBuilder;
    }
}
