<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 20/04/2018
 * Time: 09:54
 */

namespace Marello\Bundle\MageBridgeBundle\Provider;

use Marello\Bundle\MageBridgeBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class MagentoStoreList
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $client;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function getMagentoStoreList()
    {
        $wsdl = $this->getWsdl();
        $apiKey = $this->getApiKey();
        $apiUser = $this->getApiUser();

        $result = [];
        try {
            $this->client = new \SoapClient($wsdl);
            $sessionId = $this->client->login((object)array('username' => $apiUser, 'apiKey' => $apiKey));
            $result = $this->client->storeList((object)array('sessionId' => $sessionId->result));
            $result = json_decode(json_encode($result), true);
            $result = $result['result']['complexObjectArray'];

            $formatedResult = [];
            $websiteIds = [];
            foreach($result as $_data) {
                $currentWebsiteId = $_data['website_id'];
                $currentStoreId = $_data['store_id'];
                if (in_array($currentWebsiteId, $websiteIds)) {
                    continue;
                }
                $websiteIds[$currentWebsiteId] = $currentWebsiteId;
                $formatedResult[$currentWebsiteId.'-'.$currentStoreId] = $_data['name'];
            }
            $result = $formatedResult;
        } catch (\SoapFault $e) {
            //TODO: handle exception
        }
        return $result;
    }

    private function getWsdl()
    {
        $baseUrl = $this->configManager->get(Configuration::getConfigKeyByName(Configuration::MAGENTO_BASE_URL));
        return $baseUrl .'/api/v2_soap/?wsdl';
    }

    private function getApiKey()
    {
        return $this->configManager->get(Configuration::getConfigKeyByName(Configuration::MAGENTO_API_KEY));
    }

    private function getApiUser()
    {
        return $this->configManager->get( Configuration::getConfigKeyByName(Configuration::MAGENTO_API_USER));
    }
}
