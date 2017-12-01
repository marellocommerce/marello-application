<?php

namespace Marello\Bundle\ShippingBundle\Integration;

class ShippingServiceRegistry
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
     * @param string $service
     *
     * @return ShippingServiceIntegrationInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getIntegration($service)
    {
        if (!$this->hasIntegration($service)) {
            throw new \InvalidArgumentException(
                sprintf('No shipping service integration for "%s" integration.', $service)
            );
        }

        return $this->integrations[$service];
    }

    /**
     * @return ShippingServiceIntegrationInterface[]
     */
    public function getIntegrations()
    {
        return $this->integrations;
    }

    /**
     * @param string $service
     *
     * @return ShippingServiceDataFactoryInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getDataFactory($service)
    {
        if (!$this->hasDataFactory($service)) {
            throw new \InvalidArgumentException(
                sprintf('No shipping service data factory for "%s" integration.', $service)
            );
        }

        return $this->dataFactories[$service];
    }

    /**
     * @return ShippingServiceDataFactoryInterface[]
     */
    public function getDataFactories()
    {
        return $this->dataFactories;
    }
    
    /**
     * @param string $entity
     *
     * @return ShippingServiceDataProviderInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getDataProvider($entity)
    {
        if (!$this->hasDataProvider($entity)) {
            throw new \InvalidArgumentException(sprintf('No shipping service data provider for "%s" entity.', $entity));
        }

        return $this->dataProviders[$entity];
    }

    /**
     * @return ShippingServiceDataProviderInterface[]
     */
    public function getDataProviders()
    {
        return $this->dataProviders;
    }

    /**
     * @param string $service
     * @param ShippingServiceIntegrationInterface $integration
     */
    public function registerIntegration($service, ShippingServiceIntegrationInterface $integration)
    {
        $this->integrations[$service] = $integration;
    }

    /**
     * @param string $service
     * @param ShippingServiceDataFactoryInterface $factory
     */
    public function registerDataFactory($service, ShippingServiceDataFactoryInterface $factory)
    {
        $this->dataFactories[$service] = $factory;
    }
    
    /**
     * @param string $entity
     * @param ShippingServiceDataProviderInterface $provider
     */
    public function registerDataProvider($entity, ShippingServiceDataProviderInterface $provider)
    {
        $this->dataProviders[$entity] = $provider;
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    public function hasIntegration($service)
    {
        return array_key_exists($service, $this->integrations);
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    public function hasDataFactory($service)
    {
        return array_key_exists($service, $this->dataFactories);
    }
    
    /**
     * @param string $entity
     *
     * @return bool
     */
    public function hasDataProvider($entity)
    {
        return array_key_exists($entity, $this->dataProviders);
    }
}
