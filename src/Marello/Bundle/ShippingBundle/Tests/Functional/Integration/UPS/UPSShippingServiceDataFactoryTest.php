<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Service;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipper;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\ShipTo;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceDataFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class UPSShippingServiceDataFactoryTest extends WebTestCase
{
    /** @var UPSShippingServiceDataFactory */
    protected $factory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class]);

        $this->factory = $this->client->getContainer()->get('marello_shipping.integration.ups.service_data_factory');
    }

    /**
     * @test
     */
    public function testReturnedShipment()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $data = $this->factory->createData($order);

        $this->assertArrayHasKey('shipment', $data);

        /** @var Shipment $shipment */
        $shipment = $data['shipment'];

        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertInstanceOf(Shipper::class, $shipment->shipper);
        $this->assertInstanceOf(ShipTo::class, $shipment->shipTo);
        $this->assertInstanceOf(Service::class, $shipment->service);
        $this->assertInstanceOf(Package::class, $shipment->package);
    }
}
