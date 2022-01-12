<?php

namespace Marello\Bundle\PurchaseOrderBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('marello_purchaseorder');
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                'purchaseorder_notification' => ['value' => true],
                'purchaseorder_notification_address' => ['value' => 'example@example.com', 'required' => true ]
            ]
        );

        return $treeBuilder;
    }
}
