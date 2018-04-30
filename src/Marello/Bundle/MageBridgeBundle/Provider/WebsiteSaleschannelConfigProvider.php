<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 26/04/2018
 * Time: 15:43
 */

namespace Marello\Bundle\MageBridgeBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\MageBridgeBundle\Provider\MagentoStoreList;
use Marello\Bundle\MageBridgeBundle\Provider\SalesChannelProvider;

class WebsiteSaleschannelConfigProvider
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $magentoStoreProvider;

    protected $salesChannelProvider;

    public function __construct(
        ConfigManager $configManager,
        MagentoStoreList $magentoStoreList,
        SalesChannelProvider $salesChannelProvider
    )
    {
        $this->configManager = $configManager;
        $this->magentoStoreProvider = $magentoStoreList;
        $this->salesChannelProvider = $salesChannelProvider;
    }

    public function getData()
    {
        return $this->getConfigData();
    }

    /**
     * Data format stored in configuration -> [channel_id => [website_id, store_id]]
     * TODO: replace with data from the configuration
     */
    private function getConfigData()
    {
        $magentoStores = array_keys($this->magentoStoreProvider->getMagentoStoreList());
        $salesChannels = array_keys($this->salesChannelProvider->getSalesChannelList());

        $cookedData = [];
        for ($i = 0; $i < count($magentoStores); $i++) {
            $magentoStoreData = explode("-", $magentoStores[$i]);
            $cookedData[$salesChannels[$i]] = [
                'website_id' => $magentoStoreData[0],
                'default_store_id' => $magentoStoreData[1]
            ];
        }

        return $cookedData;
    }
}
