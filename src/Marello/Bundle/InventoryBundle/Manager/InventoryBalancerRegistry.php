<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class InventoryBalancerRegistry
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    protected $balancers = [];

    /**
     * InventoryBalancerRegistry constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->balancers = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     * @param $serviceAlias
     * @return InventoryBalancerInterface
     * @throws \Exception
     */
    public function getInventoryBalancer($serviceAlias)
    {
        if (!$this->balancers->containsKey($serviceAlias)) {
            throw new \Exception(sprintf('No registered balancer found for alias "%s".', $serviceAlias));
        }

        return $this->container->get($this->balancers->get($serviceAlias));
    }

    /**
     * {@inheritdoc}
     * @return array|ArrayCollection
     */
    public function getRegisteredInventoryBalancers()
    {
        return $this->balancers;
    }

    /**
     * {@inheritdoc}
     * @param $service
     * @param $alias
     * @return $this
     * @throws \Exception
     */
    public function registerInventoryBalancer($alias, $service)
    {
        if (!$this->balancers->containsKey($alias)) {
            $this->balancers->set($alias, $service);
        } else {
            throw new \Exception(sprintf('Trying to redeclare balancer alias "%s".', $alias));
        }

        return $this;
    }
}
