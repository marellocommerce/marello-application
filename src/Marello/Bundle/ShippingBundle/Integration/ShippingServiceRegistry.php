<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ShippingServiceRegistry
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    protected $integrations = [];

    /** @var array */
    protected $dataFactories = [];

    /**
     * ShippingServiceRegistry constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $service
     *
     * @return ShippingServiceIntegrationInterface
     *
     * @throws \Exception
     */
    public function getIntegration($service)
    {
        if (!$this->hasIntegration($service)) {
            throw new \Exception(sprintf('No shipping service integration for "%s" integration.', $service));
        }

        return $this->container->get($this->integrations[$service]);
    }

    /**
     * @param string $service
     *
     * @return ShippingServiceDataFactoryInterface
     *
     * @throws \Exception
     */
    public function getDataFactory($service)
    {
        if (!$this->hasDataFactory($service)) {
            throw new \Exception(sprintf('No shipping service data factory for "%s" integration.', $service));
        }

        return $this->container->get($this->dataFactories[$service]);
    }

    /**
     * @param string $service
     * @param string $id
     */
    public function registerIntegration($service, $id)
    {
        $this->integrations[$service] = $id;
    }

    /**
     * @param string $service
     * @param string $id
     */
    public function registerDataFactory($service, $id)
    {
        $this->dataFactories[$service] = $id;
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    protected function hasIntegration($service)
    {
        return array_key_exists($service, $this->integrations);
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    protected function hasDataFactory($service)
    {
        return array_key_exists($service, $this->dataFactories);
    }
}
