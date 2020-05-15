<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Form\Type\TransportSettingFormType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\FactoryInterface as RestClientFactoryInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RestTransport implements Magento2TransportInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const ALL_STORE_VIEW_CODE = 'all';
    private const API_URL_PREFIX = 'rest';
    private const API_VERSION = 'V1';

    /**
     * @var Magento2Transport
     */
    protected $transportEntity;

    /**
     * @var RestClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var RestClientInterface
     */
    protected $client;

    /**
     * @param RestClientFactoryInterface   $clientFactory
     */
    public function __construct(RestClientFactoryInterface $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function init(Transport $transportEntity): void
    {
        $this->initWithExtraOptions($transportEntity, []);
    }

    /**
     * {@inheritDoc}
     */
    public function initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions)
    {
        $this->transportEntity = $transportEntity;
        $this->client = $this->clientFactory->getClientInstance(
            new RestTransportAdapter($this->transportEntity, $clientExtraOptions)
        );
    }

    /**
     * @param string $resourceUrn
     * @param string|null $storeCode
     * @return string
     */
    protected function getFullAPIUrn(string $resourceUrn, string $storeCode = null): string
    {
        if (null === $storeCode) {
            $storeCode = self::ALL_STORE_VIEW_CODE;
        }

        return self::API_URL_PREFIX . '/' . $storeCode . '/' . self::API_VERSION . '/' . $resourceUrn;
    }

    /**
     * {@inheritDoc}
     */
    public function getWebsites(): array
    {
        $resourceUrn = $this->getFullAPIUrn('store/websites');
        try {
            /**
             * @todo Refactor this to use iterator
             */
            return $this->getClient()->get($resourceUrn)->json();
        } catch (RestException $e) {
            return $this->handleException($e, 'getWebsites');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.magento2.transport.rest.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsFormType()
    {
        return TransportSettingFormType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsEntityFQCN()
    {
        return Magento2Transport::class;
    }

    /**
     * @return RestClientInterface
     *
     * @throws RuntimeException
     */
    protected function getClient()
    {
        if (null === $this->client) {
            throw new RuntimeException("[Magento 2] REST Transport isn't configured properly.");
        }

        return $this->client;
    }

    /**
     * @param RestException $exception
     * @param string        $methodName
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function handleException(RestException $exception, string $methodName)
    {
        /**
         * Exception caused by incorrect client settings or invalid response body
         *
         * @todo think about sanitazing exception message
         */
        if (null === $exception->getResponse()) {
            throw new RuntimeException(
                '[Magento 2] ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        throw new RuntimeException(
            sprintf(
                '[Magento 2] Server returned unexpected response. Response code %s',
                $exception->getCode()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
