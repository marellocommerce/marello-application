<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSIntegrationException;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceDataFactory;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceIntegration;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class UPSShippingServiceIntegrationTest extends WebTestCase
{

    /** @var UPSShippingServiceIntegration */
    protected $integration;

    /** @var UPSShippingServiceDataFactory */
    protected $dataFactory;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class]);

        $this->dataFactory = $this->getContainer()->get('marello_shipping.integration.ups.service_data_factory');
        $this->integration = $this->getContainer()->get('marello_shipping.integration.ups.service_integration');
    }

    /**
     * @test
     * @covers UPSShippingServiceIntegration::createShipment
     */
    public function requestShipmentThrowsException()
    {
        $this->markTestSkipped();
    }
}
