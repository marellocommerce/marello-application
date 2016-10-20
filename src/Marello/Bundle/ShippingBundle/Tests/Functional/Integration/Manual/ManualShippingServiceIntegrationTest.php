<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceDataFactory;
use Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceIntegration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class ManualShippingServiceIntegrationTest extends WebTestCase
{

    /** @var ManualShippingServiceIntegration */
    protected $integration;

    /** @var ManualShippingServiceDataFactory */
    protected $dataFactory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class, LoadReturnData::class]);

        $this->dataFactory = $this->getContainer()->get('marello_shipping.integration.manual.service_data_factory');
        $this->integration = $this->getContainer()->get('marello_shipping.integration.manual.service_integration');
    }

    public function testIntegrationOrder()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $shippingDataProvider = $this->client->getContainer()->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider->setEntity($order)->setWarehouse($this->getReference('marello_warehouse_default'));

        $data = $this->dataFactory->createData($shippingDataProvider);
        
        $integration = $this->client->getContainer()->get('marello_shipping.integration.manual.service_integration');
        $shipment = $integration->createShipment($order, $data);

        $this->assertInstanceOf(Shipment::class, $shipment);
    }
    
    public function testIntegrationReturn()
    {
        /** @var ReturnEntity $return */
        $return = $this->getReference('marello_return_1');

        $shippingDataProvider = $this->client->getContainer()->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider->setEntity($return)->setWarehouse($this->getReference('marello_warehouse_default'));

        $data = $this->dataFactory->createData($shippingDataProvider);
        
        $integration = $this->client->getContainer()->get('marello_shipping.integration.manual.service_integration');
        $shipment = $integration->createShipment($return, $data);

        $this->assertInstanceOf(Shipment::class, $shipment);
    }
}
