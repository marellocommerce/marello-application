<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\RestTransportSettingsInterface;

/**
 * Class RestTransportAdapter converts entity to interface suitable for REST client factory
 */
class RestTransportAdapter implements RestTransportSettingsInterface
{
    private const TOKEN_HEADER_KEY  = 'Authorization';
    private const TOKEN_MASK        = 'Bearer %s';

    /** @var Magento2TransportSettings */
    protected $settingsBag;

    /** @var array */
    protected $additionalParams;

    /** @var array */
    protected $defaultParams = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]
    ];

    /**
     * @param Magento2TransportSettings $settingsBag
     * @param array $additionalParams
     */
    public function __construct(Magento2TransportSettings $settingsBag, array $additionalParams = [])
    {
        $this->settingsBag = $settingsBag;
        $this->additionalParams = $additionalParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return rtrim($this->settingsBag->getApiUrl(), '/') . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return array_merge_recursive(
            $this->defaultParams,
            $this->additionalParams,
            [
                'headers' => [
                    self::TOKEN_HEADER_KEY => sprintf(self::TOKEN_MASK, $this->settingsBag->getApiToken())
                ]
            ]
        );
    }
}
