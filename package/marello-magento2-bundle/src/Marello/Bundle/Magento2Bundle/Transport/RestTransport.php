<?php

namespace Marello\Bundle\Magento2Bundle\Transport;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Form\Type\TransportSettingFormType;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator\StoreIterator;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator\WebsiteIterator;
use Marello\Bundle\Magento2Bundle\Transport\Rest\RequestFactory;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\FactoryInterface as RestClientFactoryInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RestTransport implements Magento2TransportInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const RESOURCE_WEBSITES = 'store/websites';
    public const RESOURCE_STORES = 'store/storeViews';
    public const RESOURCE_STORE_CONFIGS = 'store/storeConfigs';
    public const RESOURCE_PRODUCTS = 'products';
    public const RESOURCE_PRODUCT_WITH_SKU = 'products/%sku%';

    /**
     * @var Magento2TransportSettings
     */
    protected $settingsBag;

    /**
     * @var RestClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var RestClientInterface
     */
    protected $client;

    /**
     * @param RestClientFactoryInterface $clientFactory
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        RestClientFactoryInterface $clientFactory,
        RequestFactory $requestFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->requestFactory = $requestFactory;
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
        $this->settingsBag = $transportEntity->getSettingsBag();
        $this->client = $this->clientFactory->getClientInstance(
            new RestTransportAdapter(
                $this->settingsBag,
                $clientExtraOptions
            )
        );
    }

    /**
     * @param Magento2TransportSettings $settingsBag
     * @param array $clientExtraOptions
     */
    public function initWithSettingBag(Magento2TransportSettings $settingsBag, array $clientExtraOptions = [])
    {
        $this->settingsBag = $settingsBag;
        $this->client = $this->clientFactory->getClientInstance(
            new RestTransportAdapter(
                $this->settingsBag,
                $clientExtraOptions
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getWebsites(): \Iterator
    {
        $request = $this->requestFactory->creategetRequest(
            RequestFactory::METHOD_GET,
            self::RESOURCE_WEBSITES
        );

        $data = $this->getClient()->getJSON(
            $request->getUrn()
        );

        return new WebsiteIterator($data, $this->settingsBag);
    }

    /**
     * @return \Iterator
     * @throws RestException
     * @throws RuntimeException
     */
    public function getStores(): \Iterator
    {
        $storeConfigData = $this->getStoreConfigs();

        $request = $this->requestFactory->creategetRequest(
            RequestFactory::METHOD_GET,
            self::RESOURCE_STORES
        );

        $storeData = $this->getClient()->getJSON(
            $request->getUrn()
        );

        return new StoreIterator($storeData, $storeConfigData);
    }

    /**
     * @param array $data
     * @return array
     * @throws RestException
     * @throws RuntimeException
     */
    public function createProduct(array $data): array
    {
        $request = $this->requestFactory->creategetRequest(
            RequestFactory::METHOD_POST,
            self::RESOURCE_PRODUCTS,
            [],
            $data
        );

        $result = $this->getClient()->post($request->getUrn(), $request->getPayloadData());

        return $result->json();
    }

    /**
     * @param string $sku
     * @param array $data
     * @return array
     * @throws RestException
     * @throws RuntimeException
     */
    public function updateProduct(string $sku, array $data): array
    {
        $resource = str_replace('%sku%', $sku, self::RESOURCE_PRODUCT_WITH_SKU);

        $request = $this->requestFactory->creategetRequest(
            RequestFactory::METHOD_POST,
            $resource,
            [],
            $data
        );

        $result = $this->getClient()->put($request->getUrn(), $request->getPayloadData());

        return $result->json();
    }

    /**
     * @param string $sku
     * @return bool
     * @throws RestException
     * @throws RuntimeException
     */
    public function removeProduct(string $sku): bool
    {
        $resource = str_replace('%sku%', $sku, self::RESOURCE_PRODUCT_WITH_SKU);

        $request = $this->requestFactory->creategetRequest(
            RequestFactory::METHOD_DELETE,
            $resource
        );

        $this->getClient()->delete($request->getUrn());

        return true;
    }

    /**
     * @return array
     * @throws RestException
     * @throws RuntimeException
     */
    protected function getStoreConfigs(): array
    {
        $request = $this->requestFactory->creategetRequest(
            RequestFactory::METHOD_GET,
            self::RESOURCE_STORE_CONFIGS
        );

        return $this->getClient()->getJSON(
            $request->getUrn()
        );
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
}
