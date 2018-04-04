<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 16-3-18
 * Time: 10:48
 */

namespace Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner;

use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport as Integration;

trait IntegrationChannelTrait
{
    /** @var Integration $integrationChannel */
    protected $integrationChannel;

    /**
     * @return Integration
     */
    public function getIntegrationChannel()
    {
        return $this->integrationChannel;
    }

    /**
     * @param $integrationChannel
     * @return $this
     */
    public function setIntegrationChannel(Integration $integrationChannel)
    {
        $this->integrationChannel = $integrationChannel;

        return $this;
    }

    public function configureCredentials()
    {
        /** @var Integration $integrationChannel */
        $integrationChannel = $this->getIntegrationChannel();

        $this->options['client_id'] = $integrationChannel->getClientId();
        $this->options['client_secret'] = $integrationChannel->getClientSecret();

        //url's
        $apiUrl = $this->removeTrailSlash($integrationChannel->getApiUrl());
        $adminUrl = $this->removeTrailSlash($integrationChannel->getAdminUrl());

        $this->options['request_token_url'] = $apiUrl . '/oauth/initiate';
        $this->options['authorization_url'] = $adminUrl . "/oauth_authorize";
        $this->options['access_token_url'] = $apiUrl . '/oauth/token';
        $this->options['infos_url'] = $apiUrl;

        //several M1 api
        $this->options['products'] = $apiUrl . '/api/rest/products';

        return $this;
    }

    /**
     * @param $url
     * @return string
     */
    private function removeTrailSlash($url)
    {
        return $url;
    }
}
