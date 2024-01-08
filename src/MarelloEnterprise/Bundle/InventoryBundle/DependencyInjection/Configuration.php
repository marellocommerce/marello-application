<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(MarelloEnterpriseInventoryExtension::ALIAS);
        $rootNode = $treeBuilder->getRootNode();
        SettingsBuilder::append(
            $rootNode,
            [
                'inventory_allocation_consolidation_exclusion' => [
                    'value' => []
                ]
            ]
        );

        return $treeBuilder;
    }
}
