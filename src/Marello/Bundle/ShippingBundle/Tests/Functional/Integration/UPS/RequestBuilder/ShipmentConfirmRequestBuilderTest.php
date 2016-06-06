<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS\RequestBuilder;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentConfirmRequestBuilder;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ShipmentConfirmRequestBuilderTest extends WebTestCase
{
    /** @var ShipmentConfirmRequestBuilder */
    protected $requestBuilder;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class]);

        $this->requestBuilder = $this->client
            ->getContainer()
            ->get('marello_shipping.integration.ups.request_builder.shipment_confirm');
    }

    /**
     * @test
     */
    public function testBuild()
    {
        $dataFactory = $this->client->getContainer()->get('marello_shipping.integration.ups.service_data_factory');

        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $data = $dataFactory->createData($order);

        $request = $this->requestBuilder->build($data);

        //dump($request);
    }
}
