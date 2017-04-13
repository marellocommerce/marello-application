<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentConfirmRequestBuilder;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSApi;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceDataFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class UPSApiTest extends WebTestCase
{
    /** @var UPSApi */
    protected $api;

    /** @var ShipmentConfirmRequestBuilder */
    protected $requestBuilder;

    /** @var UPSShippingServiceDataFactory */
    protected $factory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class]);

        $this->api = $this->client->getContainer()->get('marello_shipping.integration.ups.api');

        $this->requestBuilder = $this->client
            ->getContainer()
            ->get('marello_shipping.integration.ups.request_builder.shipment_confirm');

        $this->factory = $this->client->getContainer()->get('marello_shipping.integration.ups.service_data_factory');
    }

    /**
     * @test
     * @covers UPSApi::post
     */
    public function apiShouldReturnValidResponse()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $shippingDataProvider = $this->client
            ->getContainer()
            ->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider
            ->setEntity($order)
            ->setWarehouse($this->getReference('marello_warehouse_default'));

        $data = $this->factory->createData($shippingDataProvider);

        $request = $this->requestBuilder->build($data);

        $result = $this->api->post('ShipConfirm', $request);
    }
}
