<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS\RequestBuilder;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentConfirmRequestBuilder;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceDataFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ShipmentConfirmRequestBuilderTest extends WebTestCase
{
    /** @var ShipmentConfirmRequestBuilder */
    protected $requestBuilder;

    /** @var UPSShippingServiceDataFactory */
    protected $factory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class]);

        $this->requestBuilder = $this->client
            ->getContainer()
            ->get('marello_shipping.integration.ups.request_builder.shipment_confirm');

        $this->factory = $this->client->getContainer()->get('marello_shipping.integration.ups.service_data_factory');
    }

    /**
     * @test
     */
    public function testBuild()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');
        
        $shippingDataProvider = $this->client
            ->getContainer()
            ->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider->setEntity($order)->setWarehouse($this->getReference('marello_warehouse_default'));

        $data = $this->factory->createData($shippingDataProvider);

        $this->requestBuilder->build($data);
    }
}
