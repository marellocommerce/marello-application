<?php

namespace Marello\Bundle\InventoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

use Marello\Bundle\InventoryBundle\Strategy\EqualDivision\EqualDivisionBalancerStrategy;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const SYSTEM_CONFIG_PATH_BALANCE_STRATEGY = 'marello_inventory.balancing_strategy';
    const SYSTEM_CONFIG_PATH_THRESHOLD_PERCENTAGE = 'marello_inventory.balance_threshold_percentage';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(MarelloInventoryExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                'balancing_strategy' => [
                    'value' => EqualDivisionBalancerStrategy::IDENTIFIER
                ],
                'balance_threshold_percentage' => [
                    'value' => 0.20
                ],
                'po_duedate_as_back_orders_datetime' => [
                    'value' => true
                ]
            ]
        );

        return $treeBuilder;
    }
}
