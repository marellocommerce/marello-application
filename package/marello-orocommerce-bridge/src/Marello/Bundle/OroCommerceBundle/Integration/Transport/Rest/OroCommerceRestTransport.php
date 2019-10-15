<?php

namespace Marello\Bundle\OroCommerceBundle\Integration\Transport\Rest;

use Marello\Bundle\OroCommerceBundle\Client\Factory\OroCommerceRestClientFactoryInterface;
use Marello\Bundle\OroCommerceBundle\Client\OroCommerceRestClientInterface;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Form\Type\OroCommerceSettingsType;
use Marello\Bundle\OroCommerceBundle\ImportExport\Serializer\TaxCodeNormalizer;
use Marello\Bundle\OroCommerceBundle\Request\Factory\OroCommerceRequestFactory;
use Oro\Bundle\ApiBundle\Filter\FilterValue;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\PingableInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class OroCommerceRestTransport implements TransportInterface, PingableInterface
{
    const ORDERS_ALIAS = 'orders';
    const PRODUCTS_ALIAS = 'products';
    const PRODUCTIMAGES_ALIAS = 'productimages';
    const PRODUCTPRICES_ALIAS = 'productprices';
    const PRODUCTTAXCODES_ALIAS = 'producttaxcodes';
    const INVENTORYLEVELS_ALIAS = 'inventorylevels';
    const PAYMENTSTATUSES_ALIAS = 'paymentstatuses';
    const TAXVALUES_ALIAS = 'taxvalues';
    const TAXES_ALIAS = 'taxes';
    const TAXRULES_ALIAS = 'taxrules';
    const TAXJURISDICTIONS_ALIAS = 'taxjurisdictions';
    const WAREHOUSES_ALIAS = 'warehouses';

    /**
     * @var ParameterBag
     */
    private $settings;

    /**
     * @var OroCommerceRestClientFactoryInterface
     */
    private $restClientFactory;

    /**
     * @var OroCommerceRestClientInterface
     */
    private $client;

    /**
     * @param OroCommerceRestClientFactoryInterface $restClientFactory
     */
    public function setRestClientFactory(OroCommerceRestClientFactoryInterface $restClientFactory)
    {
        $this->restClientFactory = $restClientFactory;
    }

    /**
     * {@inheritDoc}
     * @return $this
     */
    public function init(Transport $transportEntity)
    {
        $this->settings = $transportEntity->getSettingsBag();
        $this->client = $this->restClientFactory->createRestClient(
            $this->settings->get(OroCommerceSettings::URL_FIELD),
            []
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsFormType()
    {
        return OroCommerceSettingsType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsEntityFQCN()
    {
        return OroCommerceSettings::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.orocommerce.orocommercesettings.label';
    }

    /**
     * @param \DateTime|null $lastSyncDate
     * @return \ArrayIterator
     */
    public function getOrders(\DateTime $lastSyncDate = null)
    {
        if (!$lastSyncDate) {
            $lastSyncDate = \DateTime::createFromFormat('j-M-Y', '15-Feb-2017');
        }
        $lastSyncStr = $lastSyncDate->format('Y-m-d H:i:s');
        $lastSyncArr = explode(' ', $lastSyncStr);
        $lastSyncStr = sprintf('%sT%s', $lastSyncArr[0], $lastSyncArr[1]);
        $ordersRequest = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::ORDERS_ALIAS,
            [
                new FilterValue(
                    'currency',
                    $this->settings->get(OroCommerceSettings::CURRENCY_FIELD),
                    OroCommerceRequestFactory::EQ
                ),
                new FilterValue(
                    'updatedAt',
                    $lastSyncStr,
                    OroCommerceRequestFactory::GT
                )
            ],
            ['customerUser', 'lineItems', 'shippingAddress', 'billingAddress']
        );

        $ordersResponse = $this->client->getJSON(
            $ordersRequest->getPath(),
            $ordersRequest->getPayload(),
            $ordersRequest->getHeaders()
        );

        $ordersTaxValuesRequest = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::TAXVALUES_ALIAS,
            [
                new FilterValue(
                    'updatedAt',
                    $lastSyncStr,
                    OroCommerceRequestFactory::GT
                ),
                new FilterValue(
                    'entityClass',
                    'Oro\Bundle\OrderBundle\Entity\Order',
                    OroCommerceRequestFactory::EQ
                )
            ]
        );

        $ordersTaxValuesResponse = $this->client->getJSON(
            $ordersTaxValuesRequest->getPath(),
            $ordersTaxValuesRequest->getPayload(),
            $ordersTaxValuesRequest->getHeaders()
        );

        $ordersData = $ordersResponse['data'];
        $taxValuesData = $ordersTaxValuesResponse['data'];
        $orders = [];
        $lineItems = [];
        $orderIds = [];
        $currency = $this->settings->get(OroCommerceSettings::CURRENCY_FIELD);
        foreach ($ordersData as $order) {
            $attributes = $order['attributes'];
            if ($attributes['currency'] === $currency) {
                $orderIds[] = $order['id'];
                if (isset($order['relationships'])) {
                    foreach ($order['relationships'] as &$relationship) {
                        foreach ($ordersResponse['included'] as $included) {
                            if (!isset($relationship['data']['type'])) {
                                if (is_array($relationship['data'])) {
                                    foreach ($relationship['data'] as $key => $data) {
                                        if ($data['type'] === 'orderlineitems') {
                                            $lineItems[$data['id']] = $data['id'];
                                        }
                                        if ($data['type'] === $included['type'] && $data['id'] === $included['id']) {
                                            $relationship['data'][$key] = $included;
                                            break;
                                        }
                                    }
                                }
                            } elseif ($relationship['data']['type'] === $included['type'] &&
                                $relationship['data']['id'] === $included['id']
                            ) {
                                $relationship['data'] = $included;
                                break;
                            }
                        }
                        unset($relationship);
                    }
                }
                foreach ($taxValuesData as $taxValue) {
                    if (isset($taxValue['attributes']['entityClass']) &&
                        isset($taxValue['attributes']['entityId']) &&
                        $taxValue['attributes']['entityClass'] === 'Oro\Bundle\OrderBundle\Entity\Order' &&
                        (int)$taxValue['attributes']['entityId'] === (int)$order['id']
                    ) {
                        $order['relationships'][self::TAXVALUES_ALIAS]['data'] = $taxValue;
                    }
                }
                $orders[] = $order;
            }
        }
        if (count($orderIds) > 0) {
            $paymentStatusesRequest = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::PAYMENTSTATUSES_ALIAS,
                [
                    new FilterValue(
                        'entityIdentifier',
                        $orderIds,
                        OroCommerceRequestFactory::EQ
                    ),
                    new FilterValue(
                        'entityClass',
                        'Oro\Bundle\OrderBundle\Entity\Order',
                        OroCommerceRequestFactory::EQ
                    )
                ]
            );

            $paymentStatusesResponse = $this->client->getJSON(
                $paymentStatusesRequest->getPath(),
                $paymentStatusesRequest->getPayload(),
                $paymentStatusesRequest->getHeaders()
            );
            $paymentStatusesData = $paymentStatusesResponse['data'];
        }
        if (count($lineItems) > 0) {
            $lineItemsTaxValuesRequest = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::TAXVALUES_ALIAS,
                [
                    new FilterValue(
                        'entityId',
                        $lineItems,
                        OroCommerceRequestFactory::EQ
                    ),
                    new FilterValue(
                        'entityClass',
                        'Oro\Bundle\OrderBundle\Entity\OrderLineItem',
                        OroCommerceRequestFactory::EQ
                    )
                ]
            );

            $lineItemsTaxValuesResponse = $this->client->getJSON(
                $lineItemsTaxValuesRequest->getPath(),
                $lineItemsTaxValuesRequest->getPayload(),
                $lineItemsTaxValuesRequest->getHeaders()
            );
            $taxValuesData = $lineItemsTaxValuesResponse['data'];
        }
        if (isset($paymentStatusesData) || isset($taxValuesData)) {
            foreach ($orders as &$order) {
                if (isset($paymentStatusesData) && count($paymentStatusesData) > 0) {
                    foreach ($paymentStatusesData as $paymentStatus) {
                        if (isset($paymentStatus['attributes']['entityClass']) &&
                            isset($paymentStatus['attributes']['entityIdentifier']) &&
                            $paymentStatus['attributes']['entityClass'] === 'Oro\Bundle\OrderBundle\Entity\Order' &&
                            (int)$paymentStatus['attributes']['entityIdentifier'] === (int)$order['id']
                        ) {
                            $order['relationships']['paymentStatus']['data'] =
                                $paymentStatus;
                        }
                    }
                }
                if (isset($taxValuesData)) {
                    $lineItems = $order['relationships']['lineItems']['data'];
                    foreach ($lineItems as $k => $lineItem) {
                        foreach ($taxValuesData as $taxValue) {
                            if (isset($taxValue['attributes']['entityClass']) &&
                                isset($taxValue['attributes']['entityId']) &&
                                $taxValue['attributes']['entityClass'] ===
                                'Oro\Bundle\OrderBundle\Entity\OrderLineItem' &&
                                (int)$taxValue['attributes']['entityId'] === (int)$lineItem['id']
                            ) {
                                $order['relationships']['lineItems']['data'][$k]['relationships'][self::TAXVALUES_ALIAS]['data'] =
                                    $taxValue;
                            }
                        }
                    }
                }
                unset($order);
            }
        }

        $obj = new \ArrayObject($orders);

        return $obj->getIterator();
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateOrder(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::ORDERS_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        $json = $response->json();

        return $json;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createPaymentStatus(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::PAYMENTSTATUSES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        $json = $response->json();

        return $json;
    }
    /**
     * @param array $data
     * @return array
     */
    public function getProductWithTaxCodeData(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::PRODUCTS_ALIAS,
            [],
            ['taxCode'],
            $data
        );

        $json = $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        foreach ($json['included'] as $included) {
            if ($included['type'] === self::PRODUCTTAXCODES_ALIAS &&
                (int)$included['id'] === (int)$json['data']['relationships']['taxCode']['data']['id']) {
                $json['data']['relationships']['taxCode']['data']['attributes'] = $included['attributes'];
            }
        }

        return $json;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createProduct(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::PRODUCTS_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        $json = $response->json();
        if ($response->getStatusCode() === 201) {
            if (isset($data['data']['relationships']['taxCode']) &&
                isset($data['data']['relationships']['taxCode']['data']['id']) &&
                $data['data']['relationships']['taxCode']['data']['id'] ===
                TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID) {
                $json = $this->getProductWithTaxCodeData($json);
            }
            $productId = $json['data']['id'];
            $unitPrecisionId = $json['data']['relationships']['primaryUnitPrecision']['data']['id'];
            $inventoryFilters = [
                new FilterValue(
                    'product',
                    $productId,
                    OroCommerceRequestFactory::EQ
                ),
                new FilterValue(
                    'productUnitPrecision',
                    $unitPrecisionId,
                    OroCommerceRequestFactory::EQ
                )
            ];
            if ($this->settings->get(OroCommerceSettings::ENTERPRISE_FIELD) &&
                $this->settings->get(OroCommerceSettings::WAREHOUSE_FIELD)) {
                $inventoryFilters[] = new FilterValue(
                    'warehouse',
                    $this->settings->get(OroCommerceSettings::WAREHOUSE_FIELD),
                    OroCommerceRequestFactory::EQ
                );
            }
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::INVENTORYLEVELS_ALIAS,
                $inventoryFilters,
                [],
                []
            );
            $inventoryResponse = $this->client->getJSON(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            if (isset($inventoryResponse['data'])) {
                $json['data']['relationships']['inventoryLevel']['data'] = reset($inventoryResponse['data']);
            }

            return $json;
        }

        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateProduct(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::PRODUCTS_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        $json = $response->json();
        if ($response->getStatusCode() === 200) {
            if (isset($data['data']['relationships']['taxCode']) &&
                isset($data['data']['relationships']['taxCode']['data']['id']) &&
                $data['data']['relationships']['taxCode']['data']['id'] ===
                TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID) {
                $json = $this->getProductWithTaxCodeData($json);
            }
        }

        return $json;
    }

    /**
     * @param int $id
     * @return RestResponseInterface
     */
    public function deleteProduct($id)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::PRODUCTS_ALIAS,
            [],
            [],
            [
                'data' => [
                    'id' => $id
                ]
            ]
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createProductPrice(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::PRODUCTPRICES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        if ($response->getStatusCode() === 201) {
            $json = $response->json();
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::PRODUCTPRICES_ALIAS,
                [],
                ['product'],
                $json
            );
            $response = $this->client->get(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        }

        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateProductPrice(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::PRODUCTPRICES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param array $data
     * @return array
     */
    public function createProductImage(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::PRODUCTIMAGES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        if ($response->getStatusCode() === 201) {
            $json = $response->json();
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::PRODUCTIMAGES_ALIAS,
                [],
                ['product'],
                $json
            );
            $response = $this->client->get(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        }

        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateProductImage(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::PRODUCTIMAGES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param int $id
     * @return RestResponseInterface
     */
    public function deleteProductImage($id)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::PRODUCTIMAGES_ALIAS,
            [],
            [],
            [
                'data' => [
                    'id' => $id
                ]
            ]
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateInventoryLevel(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::INVENTORYLEVELS_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        $json = $response->json();

        return $json;
    }

    /**
     * @return array
     */
    public function getProductUnits()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            'productunits',
            [],
            [],
            []
        );

        return $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
    }

    /**
     * @return array
     */
    public function getCustomerTaxCodes()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            'customertaxcodes',
            [],
            [],
            []
        );

        return $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
    }

    /**
     * @return array
     */
    public function getPriceLists()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            'pricelists',
            [],
            [],
            []
        );

        $json =  $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        foreach ($json['data'] as $key => $item) {
            $currency = $this->settings->get(OroCommerceSettings::CURRENCY_FIELD);
            if (!in_array($currency, $item['attributes']['priceListCurrencies'])) {
                unset($json['data'][$key]);
            }
        }

        return $json;
    }

    /**
     * @return array
     */
    public function getProductFamilies()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            'attributefamilies',
            [
                new FilterValue(
                    'entityClass',
                    'Oro\Bundle\ProductBundle\Entity\Product',
                    OroCommerceRequestFactory::EQ
                )
            ],
            ['labels'],
            []
        );

        $json =  $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        foreach ($json['data'] as $key => $item) {
            foreach ($json['included'] as $included) {
                if ((int)$included['id'] === (int)$item['relationships']['labels']['data'][0]['id']) {
                    $json['data'][$key]['attributes']['label'] = $included['attributes']['string'];
                }
            }
        }

        return $json;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createProductTaxCode(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::PRODUCTTAXCODES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateProductTaxCode(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::PRODUCTTAXCODES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param int $id
     * @return RestResponseInterface
     */
    public function deleteProductTaxCode($id)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::PRODUCTTAXCODES_ALIAS,
            [],
            [],
            [
                'data' => [
                    'id' => $id
                ]
            ]
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createTax(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::TAXES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateTax(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::TAXES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param int $id
     * @return RestResponseInterface
     */
    public function deleteTax($id)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::TAXES_ALIAS,
            [],
            [],
            [
                'data' => [
                    'id' => $id
                ]
            ]
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createTaxJurisdiction(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::TAXJURISDICTIONS_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateTaxJurisdiction(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::TAXJURISDICTIONS_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        return $response->json();
    }

    /**
     * @param int $id
     * @return RestResponseInterface
     */
    public function deleteTaxJurisdiction($id)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::TAXJURISDICTIONS_ALIAS,
            [],
            [],
            [
                'data' => [
                    'id' => $id
                ]
            ]
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
    }

    /**
     * @return array
     */
    public function getTaxRules()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::TAXRULES_ALIAS,
            [],
            [],
            []
        );

        return $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
    }

    /**
     * @param array $data
     * @return array
     */
    public function createTaxRule(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            self::TAXRULES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->post(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
        if ($response->getStatusCode() === 201) {
            $json = $response->json();
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::TAXRULES_ALIAS,
                [],
                ['productTaxCode', 'tax', 'taxJurisdiction'],
                $json
            );
            $response = $this->client->get(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        }

        return [];
    }

    /**
     * @param array $data
     * @return array
     */
    public function updateTaxRule(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            self::TAXRULES_ALIAS,
            [],
            [],
            $data
        );

        $response = $this->client->patch(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );

        if ($response->getStatusCode() === 200) {
            $json = $response->json();
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::TAXRULES_ALIAS,
                [],
                ['productTaxCode', 'tax', 'taxJurisdiction'],
                $json
            );
            $response = $this->client->get(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        }

        return [];
    }

    /**
     * @param int $id
     * @return RestResponseInterface
     */
    public function deleteTaxRule($id)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::TAXRULES_ALIAS,
            [],
            [],
            [
                'data' => [
                    'id' => $id
                ]
            ]
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
    }

    /**
     * @return array
     */
    public function getWarehouses()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::WAREHOUSES_ALIAS,
            [],
            [],
            []
        );

        return $this->client->getJSON(
            $request->getPath(),
            $request->getPayload(),
            $request->getHeaders()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        try {
            $this->getTaxRules();
            if ($this->settings->get(OroCommerceSettings::ENTERPRISE_FIELD)) {
                $result = $this->getWarehouses();
                if (empty($result) || empty($result['data'])) {
                    return [
                        'result' => false,
                        'message' =>
                            'For integration with OroCommerce EE
                             at least one warehouse should be created on OroCommerce side'
                    ];
                }
            }
        } catch (RestException $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }

        return ['result' => true, 'message' => 'Connection is valid'];
    }
}
