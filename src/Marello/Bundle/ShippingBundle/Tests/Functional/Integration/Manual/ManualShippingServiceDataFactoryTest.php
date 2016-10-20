<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData;
use Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Service;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipper;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\ShipTo;
use Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceDataFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class ManualShippingServiceDataFactoryTest extends WebTestCase
{
    /** @var ManualShippingServiceDataFactory */
    protected $factory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class, LoadReturnData::class]);
        
        $this->factory = $this->client->getContainer()->get('marello_shipping.integration.manual.service_data_factory');
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
        
        $this->assertSame([], $data);
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
        
        $this->assertSame([], $data);
    }
}
