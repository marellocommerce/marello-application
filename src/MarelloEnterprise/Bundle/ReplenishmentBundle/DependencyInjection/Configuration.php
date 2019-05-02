<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('marello_replenishment');

        SettingsBuilder::append(
            $rootNode,
            [
                'replenishment_carrier_email' => ['value' => null]
            ]
        );

        return $treeBuilder;
    }
}
