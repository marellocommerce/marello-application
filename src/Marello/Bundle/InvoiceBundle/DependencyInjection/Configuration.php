<?php

namespace Marello\Bundle\InvoiceBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(MarelloInvoiceExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                'auto_invoicing' => [
                    'value' => true
                ]
            ]
        );

        return $treeBuilder;
    }
}
