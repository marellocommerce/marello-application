<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Integration;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;

class ShippingServiceRegistryTest extends TestCase
{
    /**
     * @var ShippingServiceIntegrationInterface[]
     */
    protected $integrations = [];

    /**
     * @var ShippingServiceDataFactoryInterface[]
     */
    protected $dataFactories = [];

    /**
     * @var ShippingServiceDataProviderInterface[]
     */
    protected $dataProviders = [];
    /**
     * @var ShippingServiceRegistry
     */
    protected $shippingServiceRegistry;

    protected function setUp(): void
    {
        $this->integrations = [
            'type1' => $this->createMock(ShippingServiceIntegrationInterface::class),
            'type2' => $this->createMock(ShippingServiceIntegrationInterface::class),
            'type3' => $this->createMock(ShippingServiceIntegrationInterface::class)
        ];
        $this->dataFactories = [
            'type1' => $this->createMock(ShippingServiceDataFactoryInterface::class),
            'type2' => $this->createMock(ShippingServiceDataFactoryInterface::class),
            'type3' => $this->createMock(ShippingServiceDataFactoryInterface::class)
        ];
        $this->dataProviders = [
            'entity1' => $this->createMock(ShippingServiceDataProviderInterface::class),
            'entity2' => $this->createMock(ShippingServiceDataProviderInterface::class),
            'entity3' => $this->createMock(ShippingServiceDataProviderInterface::class)
        ];

        $this->shippingServiceRegistry = new ShippingServiceRegistry();
        foreach ($this->integrations as $service => $integration) {
            $this->shippingServiceRegistry->registerIntegration($service, $integration);
        }
        foreach ($this->dataFactories as $service => $factory) {
            $this->shippingServiceRegistry->registerDataFactory($service, $factory);
        }
        foreach ($this->dataProviders as $entity => $provider) {
            $this->shippingServiceRegistry->registerDataProvider($entity, $provider);
        }
    }

    public function testGetIntegration()
    {
        foreach ($this->integrations as $service => $integration) {
            static::assertEquals($integration, $this->shippingServiceRegistry->getIntegration($service));
        }
    }

    public function testGetNotExistingIntegration()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No shipping service integration for "wrong_service" integration.');
        $this->shippingServiceRegistry->getIntegration('wrong_service');
    }

    public function testGetIntegrations()
    {
        static::assertEquals($this->integrations, $this->shippingServiceRegistry->getIntegrations());
    }

    public function testGetDataFactory()
    {
        foreach ($this->dataFactories as $service => $factory) {
            static::assertEquals($factory, $this->shippingServiceRegistry->getDataFactory($service));
        }
    }

    public function testGetNotExistingDataFactory()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No shipping service data factory for "wrong_service" integration.');
        $this->shippingServiceRegistry->getDataFactory('wrong_service');
    }

    public function testGetDataFactories()
    {
        static::assertEquals($this->dataFactories, $this->shippingServiceRegistry->getDataFactories());
    }

    public function testGetDataProvider()
    {
        foreach ($this->dataProviders as $entity => $provider) {
            static::assertEquals($provider, $this->shippingServiceRegistry->getDataProvider($entity));
        }
    }

    public function testGetNotExistingDataProvider()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No shipping service data provider for "wrong_entity" entity.');
        $this->shippingServiceRegistry->getDataProvider('wrong_entity');
    }

    public function testGetDataProviders()
    {
        static::assertEquals($this->dataProviders, $this->shippingServiceRegistry->getDataProviders());
    }
}
