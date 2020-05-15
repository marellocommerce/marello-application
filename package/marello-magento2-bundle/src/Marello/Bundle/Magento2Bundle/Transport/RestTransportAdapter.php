<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\RestTransportSettingsInterface;

/**
 * Class RestTransportAdapter converts entity to interface suitable for REST client factory
 */
class RestTransportAdapter implements RestTransportSettingsInterface
{
    private const TOKEN_HEADER_KEY  = 'Authorization';
    private const TOKEN_MASK        = 'Bearer %s';

    /** @var Magento2Transport */
    protected $transportEntity;

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
     * RestTransportAdapter constructor.
     *
     * @param Magento2Transport $transportEntity
     * @param array $additionalParams
     */
    public function __construct(Magento2Transport $transportEntity, array $additionalParams = [])
    {
        $this->transportEntity = $transportEntity;
        $this->additionalParams = $additionalParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return rtrim($this->transportEntity->getApiUrl(), '/') . '/';
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
                    self::TOKEN_HEADER_KEY => sprintf(self::TOKEN_MASK, $this->transportEntity->getApiToken())
                ]
            ]
        );
    }
}
