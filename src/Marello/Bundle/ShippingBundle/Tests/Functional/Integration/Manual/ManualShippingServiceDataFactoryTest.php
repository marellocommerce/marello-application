<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\Manual;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ShippingBundle\Integration\Manual\ManualShippingServiceDataFactory;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class ManualShippingServiceDataFactoryTest extends WebTestCase
{
    /** @var ManualShippingServiceDataFactory */
    protected $factory;

    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures(
            [
                LoadOrderData::class,
            ]
        );
        
        $this->factory = $this->client->getContainer()->get('marello_shipping.integration.manual.service_data_factory');
    }

    /**
     * @test
     */
    public function testOrderShipment()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $shippingDataProvider = $this->client
            ->getContainer()
            ->get('marello_order.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider
            ->setEntity($order)
            ->setWarehouse($this->getReference(LoadOrderData::DEFAULT_WAREHOUSE_REF));

        $data = $this->factory->createData($shippingDataProvider);
        
        $this->assertSame([], $data);
    }
    
    /**
     * @test
     */
    public function testReturnShipment()
    {
        /** @var ReturnEntity $return */
        $return = $this->getReference('return1');

        $shippingDataProvider = $this->client
            ->getContainer()
            ->get('marello_return.shipping.integration.service_data_provider');
        $shippingDataProvider = $shippingDataProvider
            ->setEntity($return)
            ->setWarehouse($this->getReference(LoadOrderData::DEFAULT_WAREHOUSE_REF));

        $data = $this->factory->createData($shippingDataProvider);
        
        $this->assertSame([], $data);
    }
}
