<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration;

use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ShippingServiceRegistryTest extends WebTestCase
{
    /** @var ShippingServiceRegistry */
    protected $registry;

    public function setUp()
    {
        $this->initClient();

        $this->registry = $this->client->getContainer()->get('marello_shipping.integration.shipping_service_registry');
    }
    
    /**
     * @test
     * @covers ShippingServiceRegistry::getIntegration
     */
    public function getIntegrationThrowsExceptionWhenNoIntegrationIsRegistered()
    {
        $this->setExpectedException(\Exception::class, 'No shipping service integration for "testing" integration.');

        $this->registry->getIntegration('testing');
    }

    /**
     * @test
     * @covers ShippingServiceRegistry::getDataFactory
     */
    public function getDataFactoryThrowsExceptionWhenNoDataFactoryIsRegistered()
    {
        $this->setExpectedException(\Exception::class, 'No shipping service data factory for "testing" integration.');

        $this->registry->getDataFactory('testing');
    }
    
    /**
     * @test
     * @covers ShippingServiceRegistry::getIntegration
     */
    public function getIntegrationReturnsUPSIntegration()
    {
        $integration = $this->registry->getIntegration('ups');

        $this->assertNotNull($integration);
        $this->assertInstanceOf(ShippingServiceIntegrationInterface::class, $integration);
    }

    /**
     * @test
     * @covers ShippingServiceRegistry::getIntegration
     */
    public function getIntegrationReturnsManualIntegration()
    {
        $integration = $this->registry->getIntegration('manual');

        $this->assertNotNull($integration);
        $this->assertInstanceOf(ShippingServiceIntegrationInterface::class, $integration);
    }

    /**
     * @test
     * @covers ShippingServiceRegistry::getDataFactory
     */
    public function getDataFactoryReturnsUPSDataFactory()
    {
        $factory = $this->registry->getDataFactory('ups');

        $this->assertNotNull($factory);
        $this->assertInstanceOf(ShippingServiceDataFactoryInterface::class, $factory);
    }

    /**
     * @test
     * @covers ShippingServiceRegistry::getDataFactory
     */
    public function getDataFactoryReturnsManualDataFactory()
    {
        $factory = $this->registry->getDataFactory('manual');

        $this->assertNotNull($factory);
        $this->assertInstanceOf(ShippingServiceDataFactoryInterface::class, $factory);
    }
}
