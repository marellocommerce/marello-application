<?php

namespace Marello\Bundle\PricingBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const VAT_SYSTEM_CONFIG_PATH = 'marello_pricing.is_vat_included';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(MarelloPricingExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                'is_vat_included' => [
                    'value' => false
                ]
            ]
        );

        return $treeBuilder;
    }
}
