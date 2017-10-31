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
                ]
            ]
        );

        return $treeBuilder;
    }
}
