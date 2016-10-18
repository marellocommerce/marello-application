<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Service;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipper;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\ShipTo;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceDataFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class UPSShippingServiceDataFactoryTest extends WebTestCase
{
    /** @var UPSShippingServiceDataFactory */
    protected $factory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class, LoadReturnData::class]);
        
        $this->factory = $this->client->getContainer()->get('marello_shipping.integration.ups.service_data_factory');
    }

    /**
     * @test
     */
    public function testOrderShipment()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $shippingDataProvider = $this->client->getContainer()->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider->setEntity($order)->setWarehouse($this->getReference('marello_warehouse_default'));

        $data = $this->factory->createData($shippingDataProvider);

        $this->assertArrayHasKey('shipment', $data);

        /** @var Shipment $shipment */
        $shipment = $data['shipment'];

        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertInstanceOf(Shipper::class, $shipment->shipper);
        $this->assertInstanceOf(ShipTo::class, $shipment->shipTo);
        $this->assertInstanceOf(Service::class, $shipment->service);
        $this->assertInstanceOf(Package::class, $shipment->package);
    }

    /**
     * @test
     */
    public function testReturnShipment()
    {
        /** @var ReturnEntity $return */
        $return = $this->getReference('marello_return_1');

        $shippingDataProvider = $this->client->getContainer()->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider->setEntity($return)->setWarehouse($this->getReference('marello_warehouse_default'));

        $data = $this->factory->createData($shippingDataProvider);

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
