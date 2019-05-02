<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Client\Factory;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientFactoryInterface;

class GoogleApiClientFactory implements GoogleApiClientFactoryInterface
{
    const PARAM_API_KEY = 'key';
    const GOOGLE_INTEGRATION_CLIENT_SECRET = 'oro_google_integration.google_api_key';

    /**
     * @var RestClientFactoryInterface
     */
    private $restClientFactory;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var string
     */
    private $url;

    /**
     * @param RestClientFactoryInterface $restClientFactory
     * @param ConfigManager $configManager
     * @param string $url
     */
    public function __construct(
        RestClientFactoryInterface $restClientFactory,
        ConfigManager $configManager,
        $url
    ) {
        $this->restClientFactory = $restClientFactory;
        $this->configManager = $configManager;
        $this->url = $url;
    }

    /**
     * {@inheritDoc}
     */
    public function createClient()
    {
        return $this->restClientFactory->createRestClient(
            $this->url,
            [
                'query' => [
                    self::PARAM_API_KEY => $this->configManager->get(self::GOOGLE_INTEGRATION_CLIENT_SECRET)
                ]
            ]
        );
    }
}
