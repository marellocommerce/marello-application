<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root(MarelloEnterpriseGoogleApiExtension::ALIAS);

        SettingsBuilder::append($rootNode, [
            'enable_google_address_geocoding' => [
                'value' => false,
                'type' => 'boolean',
            ],
            'enable_google_distance_matrix' => [
                'value' => false,
                'type' => 'boolean',
            ],
            'google_distance_matrix_mode' => [
                'value' => 'driving',
                'type' => 'string',
            ]
        ]);

        return $treeBuilder;
    }
}
