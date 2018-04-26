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
    const MAGENTO_BASE_URL = 'magento_base_url';

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
//                    'value' => [],
                    'type' => 'array'
                ],
                self::MAGENTO_API_KEY => [
                    'value' => '',
                    'type' => 'text'
                ],
                self::MAGENTO_API_USER => [
                    'value' => '',
                    'type' => 'text'
                ],
                self::MAGENTO_BASE_URL => [
                    'value' => '',
                    'type' => 'text'
                ]
            ]
        );

        return $treeBuilder;
    }

    /**
     * Returns full key name by it's last part
     *
     * @param $name string last part of the key name (one of the class cons can be used)
     * @return string full config path key
     */
    public static function getConfigKeyByName($name)
    {
        return MarelloMageBridgeExtension::ALIAS . ConfigManager::SECTION_MODEL_SEPARATOR . $name;
    }
}
