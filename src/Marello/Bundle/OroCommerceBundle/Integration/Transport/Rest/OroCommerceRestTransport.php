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
    const CUSTOMERS_ALIAS = 'customers';
    const CUSTOMERUSERS_ALIAS = 'customerusers';
    const ORDERS_ALIAS = 'orders';
    const PAYMENTTERMS_ALIAS = 'paymentterms';
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

    const COLLECTION_ALIAS = 'collection';

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
    public function getCustomers(\DateTime $lastSyncDate = null)
    {
        if (!$lastSyncDate) {
            $lastSyncDate = \DateTime::createFromFormat('j-M-Y', '15-Feb-2017');
        }
        $lastSyncStr = $lastSyncDate->format('Y-m-d H:i:s');
        $lastSyncArr = explode(' ', $lastSyncStr);
        $lastSyncStr = sprintf('%sT%s', $lastSyncArr[0], $lastSyncArr[1]);
        $customersData = [];
        $customersResponseData = [];
        $pageNumber = 1;
        do {
            $result = true;
            $customersRequest = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::CUSTOMERS_ALIAS,
                [
                    new FilterValue(
                        'updatedAt',
                        $lastSyncStr,
                        OroCommerceRequestFactory::GT
                    )
                ],
                ['addresses', 'parent', 'paymentTerm'],
                [],
                //tmp fixed pagesize
                ['pageSize' => 100, 'pageNumber' => $pageNumber]
            );

            $customersResponse = $this->client->getJSON(
                $customersRequest->getPath(),
                $customersRequest->getPayload(),
                $customersRequest->getHeaders()
            );

            $data = $customersResponse['data'];
            usort($data, function ($a, $b) {
                if ($a['relationships']['parent']['data'] === null) {
                    return -1;
                } elseif ($b['relationships']['parent']['data'] === null) {
                    return 1;
                } else {
                    return 0;
                }
            });

            if (!empty($data)) {
                $customersData = array_merge($customersData, $data);
                $pageNumber++;
            }

            if (!empty($customersResponse['included'])) {
                $customersResponseData = array_merge(
                    $customersResponseData,
                    $customersResponse['included']
                );
            }

            // no more data to look for
            if (empty($data)) {
                $result = null;
                break;
            }

            // loop again if result is true
            // true means that there are entities to process or
            // there are intervals to retrieve entities there
        } while ($result === true);

        $customers = [];
        foreach ($customersData as $customer) {
            if (isset($customer['relationships'])) {
                foreach ($customer['relationships'] as &$relationship) {
                    foreach ($customersResponseData as $included) {
                        if (!isset($relationship['data']['type'])) {
                            if (is_array($relationship['data'])) {
                                foreach ($relationship['data'] as $key => $data) {
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

            $customers[] = $customer;
        }
        $obj = new \ArrayObject($customers);

        return $obj->getIterator();
    }
    
    /**
     * @return \ArrayIterator
     */
    public function getPaymentTerms()
    {
        $paymentTermsRequest = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::PAYMENTTERMS_ALIAS,
            [],
            []
        );

        $paymentTermsResponse = $this->client->getJSON(
            $paymentTermsRequest->getPath(),
            $paymentTermsRequest->getPayload(),
            $paymentTermsRequest->getHeaders()
        );
        $obj = new \ArrayObject($paymentTermsResponse['data']);

        return $obj->getIterator();
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
        $ordersData = [];
        $ordersResponseData = [];
        $taxValuesData = [];
        $pageNumber = 1;
        do {
            $result = true;
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
                ['customer', 'customerUser', 'lineItems', 'shippingAddress', 'billingAddress'],
                [],
                ['pageNumber' => $pageNumber]
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
                ],
                [],
                [],
                ['pageNumber' => $pageNumber]
            );

            $ordersTaxValuesResponse = $this->client->getJSON(
                $ordersTaxValuesRequest->getPath(),
                $ordersTaxValuesRequest->getPayload(),
                $ordersTaxValuesRequest->getHeaders()
            );

            if (!empty($ordersResponse['data'])) {
                $ordersData = array_merge($ordersData, $ordersResponse['data']);
                $taxValuesData = array_merge($taxValuesData, $ordersTaxValuesResponse['data']);
                $pageNumber++;
            }

            if (!empty($ordersResponse['included'])) {
                $ordersResponseData = array_merge(
                    $ordersResponseData,
                    $ordersResponse['included']
                );
            }

            // no more data to look for
            if (empty($ordersResponse['data'])) {
                $result = null;
                break;
            }

            // loop again if result is true
            // true means that there are entities to process or
            // there are intervals to retrieve entities there
        } while ($result === true);

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
                        foreach ($ordersResponseData as $included) {
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
                ],
                [],
                [],
                ['pageSize' => count($orderIds)]
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
                ],
                [],
                [],
                ['pageSize' => count($lineItems)]
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
                (int)$included['id'] === (int)$json['data']['relationships']['taxCode']['data']['id']
            ) {
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
        try {
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
                    TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID
                ) {
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
                    $this->settings->get(OroCommerceSettings::WAREHOUSE_FIELD)
                ) {
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
        } catch (RestException $e) {
            return $this->processEntityDuplicationException($e, $data, 'createProduct', 'updateProduct');
        }

        return [];
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
        try {
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
                    TaxCodeNormalizer::NEW_PRODUCT_TAX_CODE_ID
                ) {
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
                    $this->settings->get(OroCommerceSettings::WAREHOUSE_FIELD)
                ) {
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
        } catch (RestException $e) {
            return $this->processEntityDuplicationException($e, $data, 'createProduct', 'updateProduct');
        }

        return [];
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
        try {
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
        } catch (RestException $e) {
            $json = $e->getResponse()->json();
            if (isset($json['errors']) && count($json['errors']) === 1) {
                $error = reset($json['errors']);
                if (isset($error['detail']) &&
                    $error['detail'] === 'Product has duplication of product prices. Set of fields "PriceList", "Quantity" , "Unit" and "Currency" should be unique.') {
                    $filters = [];
                    foreach ($data['data']['relationships'] as $k => $relationship) {
                        $filters[] = new FilterValue(
                            $k,
                            $relationship['data']['id'],
                            OroCommerceRequestFactory::EQ
                        );
                    }
                    $request = OroCommerceRequestFactory::createRequest(
                        OroCommerceRequestFactory::METHOD_GET,
                        $this->settings,
                        $data['data']['type'],
                        $filters,
                        [],
                        []
                    );
                    $existingEntity = $this->client->getJSON(
                        $request->getPath(),
                        $request->getPayload(),
                        $request->getHeaders()
                    );
                    $existingEntityId = null;
                    if (isset($existingEntity['data'])) {
                        $existingEntityData = reset($existingEntity['data']);
                        if (isset($existingEntityData['id'])) {
                            $existingEntityId = $existingEntityData['id'];
                        }
                    }
                    if ($existingEntityId) {
                        $data['data']['id'] = $existingEntityId;
                        unset($data['data']['relationships']['priceList']);
                        return $this->updateProductPrice($data);
                    } else {
                        return $this->createProductPrice($data);
                    }
                }
            } else {
                return $this->processEntityDuplicationException($e, $data, 'createProductPrice', 'updateProductPrice');
            }
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

        if ($response->getStatusCode() === 200) {
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
    public function createProductImage(array $data)
    {
        $productId = $data['data']['relationships']['product']['data']['id'];
        $existingProductImagesRequest = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            self::PRODUCTIMAGES_ALIAS,
            [
                new FilterValue(
                    'product',
                    $productId,
                    OroCommerceRequestFactory::EQ
                ),
            ],
            ['image'],
            []
        );

        $existingProductImages = $this->client->getJSON(
            $existingProductImagesRequest->getPath(),
            $existingProductImagesRequest->getPayload(),
            $existingProductImagesRequest->getHeaders()
        );
        if (isset($existingProductImages['included'])) {
            $fileAttributes = [];
            foreach ($data['included'] as $included) {
                if ($included['type'] === 'files' && isset($included['attributes'])) {
                    $fileAttributes = $included['attributes'];
                    break;
                }
            }
            foreach ($existingProductImages['included'] as $included) {
                if ($included['type'] === 'files') {
                    $fileId = $included['id'];
                    $existingImageArguments = $included['attributes'];
                    if ($existingImageArguments['mimeType'] === $fileAttributes['mimeType'] &&
                        $existingImageArguments['originalFilename'] === $fileAttributes['originalFilename'] &&
                        $existingImageArguments['fileSize'] === $fileAttributes['fileSize'] &&
                        $existingImageArguments['content'] === $fileAttributes['content']
                    ) {
                        foreach ($existingProductImages['data'] as $existingProductImage) {
                            if ($existingProductImage['relationships']['image']['data']['id'] === $fileId) {
                                $request = OroCommerceRequestFactory::createRequest(
                                    OroCommerceRequestFactory::METHOD_GET,
                                    $this->settings,
                                    self::PRODUCTIMAGES_ALIAS,
                                    [],
                                    ['product'],
                                    [
                                        'data' => [
                                            'id' => $existingProductImage['id']
                                        ]
                                    ]
                                );
                                $response = $this->client->get(
                                    $request->getPath(),
                                    $request->getPayload(),
                                    $request->getHeaders()
                                );

                                return $response->json();
                            }
                        }
                    }
                }
            }
        }
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

        if ($response->getStatusCode() === 200) {
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
    public function getBusinessUnits()
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_GET,
            $this->settings,
            'businessunits',
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

        $json = $this->client->getJSON(
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

        $json = $this->client->getJSON(
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
        try {
            $response = $this->client->post(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        } catch (RestException $e) {
            return $this->processEntityDuplicationException($e, $data, 'createProductTaxCode', 'updateProductTaxCode');
        }
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
     * @param array $ids
     * @return RestResponseInterface
     */
    public function bulkDeleteProductTaxCodes(Array $ids)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::PRODUCTTAXCODES_ALIAS,
            [
                new FilterValue(
                    'id',
                    $ids,
                    OroCommerceRequestFactory::EQ
                )
            ],
            [],
            []
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
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
        try {
            $response = $this->client->post(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        } catch (RestException $e) {
            return $this->processEntityDuplicationException($e, $data, 'createTax', 'updateTax');
        }
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
     * @param array $ids
     * @return RestResponseInterface
     */
    public function bulkDeleteTaxes(Array $ids)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::TAXES_ALIAS,
            [
                new FilterValue(
                    'id',
                    $ids,
                    OroCommerceRequestFactory::EQ
                )
            ],
            [],
            []
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
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
        try {
            $response = $this->client->post(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $response->json();
        } catch (RestException $e) {
            return $this->processEntityDuplicationException(
                $e,
                $data,
                'createTaxJurisdiction',
                'updateTaxJurisdiction'
            );
        }
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
     * @param array $ids
     * @return RestResponseInterface
     */
    public function bulkDeleteTaxJurisdictions(Array $ids)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::TAXJURISDICTIONS_ALIAS,
            [
                new FilterValue(
                    'id',
                    $ids,
                    OroCommerceRequestFactory::EQ
                )
            ],
            [],
            []
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
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
        try {
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
        } catch (RestException $e) {
            return $this->processEntityDuplicationException($e, $data, 'createTaxRule', 'updateTaxRule');
        }
    }


    /**
     * @param array $data
     * @return array
     */
    public function createTaxRulesCollection(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_POST,
            $this->settings,
            sprintf('%s/%s', self::TAXRULES_ALIAS, self::COLLECTION_ALIAS),
            [],
            [],
            $data
        );
        try {
            $response = $this->client->post(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );
            if ($response->getStatusCode() === 200) {
                $json = $response->json();
                $syncEntities = [];
                $notSyncEntities = [];
                $notSyncEntitiesErrors = [];
                foreach ($json as $key => $itemJson) {
                    if (isset($itemJson['data'])) {
                        $syncEntities[$key] = $itemJson;
                    } elseif (isset($itemJson['errors'])) {
                        $notSyncEntities[$key] = $data[$key];
                        $notSyncEntitiesErrors[$key] = $itemJson;
                    }
                }
                $finalData = [];
                if (!empty($syncEntities)) {
                    $ids = array_map(
                        function ($itemData) {
                            return $itemData['data']['id'];
                        },
                        $syncEntities
                    );
                    $request = OroCommerceRequestFactory::createRequest(
                        OroCommerceRequestFactory::METHOD_GET,
                        $this->settings,
                        self::TAXRULES_ALIAS,
                        [
                            new FilterValue(
                                'id',
                                $ids,
                                OroCommerceRequestFactory::EQ
                            )
                        ],
                        ['productTaxCode', 'tax', 'taxJurisdiction'],
                        []
                    );
                    $response = $this->client->get(
                        $request->getPath(),
                        $request->getPayload(),
                        $request->getHeaders()
                    );

                    $finalData = $this->formatCollectionData($response->json());
                }
                if (!empty($notSyncEntities)) {
                    $finalData = $this->processCollectionEntitiesDuplicationException(
                        $notSyncEntitiesErrors,
                        $notSyncEntities,
                        'createTaxRulesCollection',
                        'updateTaxRulesCollection'
                    );
                }

                return $finalData;
            }

            return [];
        } catch (RestException $e) {
            return [];
        }
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
     * @param array $data
     * @return array
     */
    public function updateTaxRulesCollection(array $data)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_PATCH,
            $this->settings,
            sprintf('%s/%s', self::TAXRULES_ALIAS, self::COLLECTION_ALIAS),
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
            $ids = array_map(
                function ($itemData) {
                    return $itemData['data']['id'];
                },
                $json
            );
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                self::TAXRULES_ALIAS,
                [
                    new FilterValue(
                        'id',
                        $ids,
                        OroCommerceRequestFactory::EQ
                    )
                ],
                ['productTaxCode', 'tax', 'taxJurisdiction'],
                []
            );
            $response = $this->client->get(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );

            return $this->formatCollectionData($response->json());
        }

        return [];
    }

    /**
     * @param array $ids
     * @return RestResponseInterface
     */
    public function bulkDeleteTaxRules(Array $ids)
    {
        $request = OroCommerceRequestFactory::createRequest(
            OroCommerceRequestFactory::METHOD_DELETE,
            $this->settings,
            self::TAXRULES_ALIAS,
            [
                new FilterValue(
                    'id',
                    $ids,
                    OroCommerceRequestFactory::EQ
                )
            ],
            [],
            []
        );

        $response = $this->client->delete(
            $request->getPath(),
            $request->getHeaders()
        );

        return $response;
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
     * @param RestException $e
     * @param array $data
     * @param string $createMethod
     * @param string $updateMethod
     * @return array
     */
    protected function processEntityDuplicationException(RestException $e, $data, $createMethod, $updateMethod)
    {
        $json = $e->getResponse()->json();
        if (isset($json['errors'])) {
            $errorInIncludedEntities = false;
            foreach ($json['errors'] as $error) {
                if (isset($error['detail']) && $error['detail'] === 'This value is already used.') {
                    if (isset($error['source']) && isset($error['source']['pointer'])) {
                        $pointersString = ltrim($error['source']['pointer'], '/');
                        if (strpos($pointersString, 'included') !== false) {
                            $errorInIncludedEntities = true;
                            break;
                        }
                    }
                }
            }
            $eJson = $e->getResponse()->json();
            if ($errorInIncludedEntities === true) {
                $data = $this->processIncludedEntitiesDuplicationException($eJson, $data);
            } else {
                $data = $this->processMainEntityDuplicationException($eJson, $data);
            }
            if (isset($data['data']['id'])) {
                return $this->$updateMethod($data);
            } else {
                return $this->$createMethod($data);
            }
        }

        return [];
    }

    /**
     * @param array $eJson
     * @param array $data
     * @return array
     */
    protected function processIncludedEntitiesDuplicationException($eJson, $data)
    {
        if (isset($eJson['errors'])) {
            foreach ($eJson['errors'] as $error) {
                if (isset($error['detail']) && $error['detail'] === 'This value is already used.') {
                    if (isset($error['source']) && isset($error['source']['pointer'])) {
                        $pointersString = ltrim($error['source']['pointer'], '/');
                        if (strpos($pointersString, 'included') !== false) {
                            $pointersArray = explode('/', $pointersString);
                            $errorSource = $data;
                            $type = null;
                            $pointer = null;
                            foreach ($pointersArray as $pointer) {
                                $errorSource = $errorSource[$pointer];
                                if (isset($errorSource['type'])) {
                                    $type = $errorSource['type'];
                                }
                            }

                            if ($errorSource && $type && $pointer) {
                                $request = OroCommerceRequestFactory::createRequest(
                                    OroCommerceRequestFactory::METHOD_GET,
                                    $this->settings,
                                    $type,
                                    [
                                        new FilterValue(
                                            $pointer,
                                            $errorSource,
                                            OroCommerceRequestFactory::EQ
                                        ),
                                    ],
                                    [],
                                    []
                                );
                                $errorSourceJson = $this->client->getJSON(
                                    $request->getPath(),
                                    $request->getPayload(),
                                    $request->getHeaders()
                                );
                                $errorSourceId = null;
                                if (isset($errorSourceJson['data'])) {
                                    $errorSourceData = reset($errorSourceJson['data']);
                                    if (isset($errorSourceData['id'])) {
                                        $errorSourceId = $errorSourceData['id'];
                                    }
                                }
                                if ($errorSourceId) {
                                    foreach ($data['data']['relationships'] as $k => $relationship) {
                                        if (isset($relationship['data']) && isset($relationship['data']['type']) &&
                                            $relationship['data']['type'] === $type
                                        ) {
                                            $data['data']['relationships'][$k]['data']['id'] = $errorSourceId;
                                            if (isset($data['included'][$pointersArray[1]]['relationships'])) {
                                                foreach ($data['included'][$pointersArray[1]]['relationships'] as $inclRelationship) {
                                                    if (isset($inclRelationship['data']['id'])) {
                                                        $relId = $inclRelationship['data']['id'];
                                                        $relType = $inclRelationship['data']['type'];
                                                        foreach ($data['included'] as $k => $included) {
                                                            if ($included['type'] === $relType &&
                                                                $included['id'] === $relId &&
                                                                count($included['relationships']) === 0
                                                            ) {
                                                                $parentRel = reset($included['relationships']);
                                                                if ($parentRel['data']['type'] === $type) {
                                                                    unset($data['included'][$k]);
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        foreach ($inclRelationship['data'] as $relItem) {
                                                            if (isset($relItem['id'])) {
                                                                $relId = $relItem['id'];
                                                                $relType = $relItem['type'];
                                                                foreach ($data['included'] as $k => $included) {
                                                                    if ($included['type'] === $relType &&
                                                                        $included['id'] === $relId &&
                                                                        count($included['relationships']) === 1
                                                                    ) {
                                                                        $parentRel = reset($included['relationships']);
                                                                        if ($parentRel['data']['type'] === $type) {
                                                                            unset($data['included'][$k]);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            unset($data['included'][$pointersArray[1]]);
                                        }
                                    }
                                    if (empty($data['included'])) {
                                        unset($data['included']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $filters = [];
            foreach ($data['data']['relationships'] as $k => $relationship) {
                if (isset($relationship['data']) && isset($relationship['data']['id']) &&
                    $relationship['data']['id'] === (string)(int)$relationship['data']['id']) {
                    $filters[] = new FilterValue(
                        $k,
                        $relationship['data']['id'],
                        OroCommerceRequestFactory::EQ
                    );
                } else {
                    return $data;
                }
            }
            $request = OroCommerceRequestFactory::createRequest(
                OroCommerceRequestFactory::METHOD_GET,
                $this->settings,
                $data['data']['type'],
                $filters,
                [],
                []
            );
            $existingEntity = $this->client->getJSON(
                $request->getPath(),
                $request->getPayload(),
                $request->getHeaders()
            );
            $existingEntityId = null;
            if (isset($existingEntity['data'])) {
                $existingEntityData = reset($existingEntity['data']);
                if (isset($existingEntityData['id'])) {
                    $existingEntityId = $existingEntityData['id'];
                }
            }
            if ($existingEntityId) {
                $data['data']['id'] = $existingEntityId;
            }
            return $data;
        }

        return [];
    }

    /**
     * @param array $eJson
     * @param array $data
     * @return array
     */
    protected function processMainEntityDuplicationException($eJson, $data)
    {
        if (isset($eJson['errors'])) {
            foreach ($eJson['errors'] as $error) {
                if (isset($error['detail']) && $error['detail'] === 'This value is already used.') {
                    if (isset($error['source']) && isset($error['source']['pointer'])) {
                        $pointersString = ltrim($error['source']['pointer'], '/');
                        $pointersArray = explode('/', $pointersString);
                        $errorSource = $data;
                        $type = null;
                        $pointer = null;
                        foreach ($pointersArray as $pointer) {
                            $errorSource = $errorSource[$pointer];
                            if (isset($errorSource['type'])) {
                                $type = $errorSource['type'];
                            }
                        }

                        if ($errorSource && $type && $pointer) {
                            $request = OroCommerceRequestFactory::createRequest(
                                OroCommerceRequestFactory::METHOD_GET,
                                $this->settings,
                                $type,
                                [
                                    new FilterValue(
                                        $pointer,
                                        $errorSource,
                                        OroCommerceRequestFactory::EQ
                                    ),
                                ],
                                [],
                                []
                            );
                            $errorSourceJson = $this->client->getJSON(
                                $request->getPath(),
                                $request->getPayload(),
                                $request->getHeaders()
                            );
                            $errorSourceId = null;
                            if (isset($errorSourceJson['data'])) {
                                $errorSourceData = reset($errorSourceJson['data']);
                                if (isset($errorSourceData['id'])) {
                                    $errorSourceId = $errorSourceData['id'];
                                }
                            }
                            if ($errorSourceId) {
                                $data['data']['id'] = $errorSourceId;
                            }

                            return $data;
                        }
                    }
                }
            }
        }

        return [];
    }

    /**
     * @param array $eArray
     * @param array $data
     * @param string $createMethod
     * @param string $updateMethod
     * @return array
     */
    protected function processCollectionEntitiesDuplicationException($eArray, $data, $createMethod, $updateMethod)
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        foreach ($eArray as $key => $json) {
            if (isset($json['errors'])) {
                $errorInIncludedEntities = false;
                foreach ($json['errors'] as $error) {
                    if (isset($error['detail']) && $error['detail'] === 'This value is already used.') {
                        if (isset($error['source']) && isset($error['source']['pointer'])) {
                            $pointersString = ltrim($error['source']['pointer'], '/');
                            if (strpos($pointersString, 'included') !== false) {
                                $errorInIncludedEntities = true;
                                break;
                            }
                        }
                    }
                }
                if ($errorInIncludedEntities === true) {
                    $processedData = $this->processIncludedEntitiesDuplicationException($json, $data[$key]);
                } else {
                    $processedData = $this->processMainEntityDuplicationException($json, $data[$key]);
                }
                if (isset($processedData['data']['id'])) {
                    $entitiesToUpdate[] = $processedData;
                } else {
                    $entitiesToCreate[] = $processedData;
                }
            }
        }
        $finalData = [];
        if (!empty($entitiesToCreate)) {
            $finalData = array_merge($finalData, $this->$createMethod($entitiesToCreate));
        }
        if (!empty($entitiesToUpdate)) {
            $finalData = array_merge($finalData, $this->$updateMethod($entitiesToUpdate));
        }

        return $finalData;
    }

    /**
     * @param array $data
     * @return array
     */
    private function formatCollectionData(array $data)
    {
        $formattedData = [];
        foreach ($data['data'] as $key => $itemData) {
            $formattedData[$key]['data'] = $itemData;
            foreach ($itemData['relationships'] as $relationship) {
                foreach ($data['included'] as $included) {
                    if ($relationship['data']['type'] === $included['type'] &&
                        $relationship['data']['id'] === $included['id']) {
                        $formattedData[$key]['included'][] = $included;
                        break;
                    }
                }
            }
        }

        return $formattedData;
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
