<?php

namespace MarelloEnterprise\Bundle\OrderBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(MarelloEnterpriseOrderExtension::ALIAS);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append($rootNode, [
            'enable_order_consolidation' => [
                'value' => false,
                'type' => 'boolean',
            ]
        ]);

        return $treeBuilder;
    }
}
