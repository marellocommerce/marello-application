<?php

namespace Marello\Bundle\Magento2Bundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROOT_NODE = MarelloMagento2Extension::ALIAS;
    public const MISS_TIMING_ASSUMPTION_INTERVAL_KEY = 'miss_timing_assumption_interval';
    public const IMPORT_SYNC_INTERVAL_KEY = 'import_sync_interval';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                /**
                 * There is possibility to have miss timing between web-nodes if Magento
                 * instance is deployed as a web farm in order to prevent loss of data sync
                 * process always include some additional time assumption.
                 * Should be Date in ISO 8601 format, current value is "5 minutes"
                 */
                self::MISS_TIMING_ASSUMPTION_INTERVAL_KEY => ['value' => 'PT300S', 'type' => 'string'],

                /**
                 * This interval will be used in initial sync, connector will walk starting from now or
                 * last initial import date and will import data from now till previous date by step interval.
                 * Should be Date in ISO 8601 format, current value is "7 days"
                 */
                self::IMPORT_SYNC_INTERVAL_KEY => ['value' => 'P7D', 'type' => 'string'],
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
        return self::ROOT_NODE . ConfigManager::SECTION_MODEL_SEPARATOR . $name;
    }
}
