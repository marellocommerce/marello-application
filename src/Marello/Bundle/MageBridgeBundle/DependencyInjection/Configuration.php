<?php

namespace Marello\Bundle\MageBridgeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    const WEBSITE_CODE_SALES_CHANNEL = 'magento_website_sales_channel';
    const MAGENTO_API_USER = 'magento_soap_api_user';
    const MAGENTO_API_KEY = 'magento_soap_api_key';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(MarelloMageBridgeExtension::ALIAS);

        SettingsBuilder::append(
            $rootNode,
            [
                self::WEBSITE_CODE_SALES_CHANNEL => [
                    'value' => [],
                    'type' => 'array'
                ],
                self::MAGENTO_API_KEY => [
                    'value' => '',
                    'type' => 'text'
                ],
                self::MAGENTO_API_USER => [
                    'value' => '',
                    'type' => 'text'
                ]
            ]
        );

        return $treeBuilder;
    }
}
