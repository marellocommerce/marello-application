<?php

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

        $this->options['oauth_token'] = $integrationChannel->getTokenKey();
        $this->options['oauth_token_secret'] = $integrationChannel->getTokenSecret();

        //url's
        $apiUrl = $this->removeTrailSlash($integrationChannel->getApiUrl());
        $adminUrl = $this->removeTrailSlash($integrationChannel->getAdminUrl());

        $this->options['request_token_url'] = $apiUrl . '/oauth/initiate';
        $this->options['authorization_url'] = $adminUrl . "/oauth_authorize";
        $this->options['access_token_url'] = $apiUrl . '/oauth/token';
        $this->options['infos_url'] = $apiUrl;


        //several M1 api
        $this->options['product_api_resource'] = $this->options['infos_url'] . self::PRODUCT_API_RESOURCE;
        $this->options['inventory_api_resource'] = $this->options['infos_url'] . self::INVENTORY_API_RESOURCE;

        return $this;
    }

    /**
     * @return array
     */
    private function getAccessTokenKeys()
    {
        $accessTokens = [
            'oauth_token' => $this->options['oauth_token'],
            'oauth_token_secret' => $this->options['oauth_token_secret']];
        return $accessTokens;
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
