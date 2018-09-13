<?php

namespace Marello\Bundle\ReturnBundle\DependencyInjection;

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
        $rootNode    = $treeBuilder->root('marello_return');

        SettingsBuilder::append(
            $rootNode,
            [
                'ror_period'          => ['value' => 30],
                'warranty_period'     => ['value' => 24],
                'return_notification' => ['value' => true]
            ]
        );

        return $treeBuilder;
    }
}
