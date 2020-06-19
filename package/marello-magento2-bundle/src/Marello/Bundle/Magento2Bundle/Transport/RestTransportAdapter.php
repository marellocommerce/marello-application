<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\RestTransportSettingsInterface;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

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
            'Accept' => 'application/json'
        ]
    ];

    /** @var string */
    protected $apiToken;

    /**
     * @param Magento2TransportSettings $settingsBag
     * @param SymmetricCrypterInterface $crypter
     * @param array $additionalParams
     */
    public function __construct(
        Magento2TransportSettings $settingsBag,
        SymmetricCrypterInterface $crypter,
        array $additionalParams = []
    ) {
        $this->settingsBag = $settingsBag;
        $this->apiToken = $crypter->decryptData($this->settingsBag->getApiToken());
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
                    self::TOKEN_HEADER_KEY => sprintf(self::TOKEN_MASK, $this->apiToken)
                ]
            ]
        );
    }
}
