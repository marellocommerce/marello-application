<?php

namespace Marello\Bundle\ReturnBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ReturnBusinessRuleRegistry
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array| */
    protected $businessRules = [];

    /**
     * ReturnBusinessRuleRegistry constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->businessRules = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     * @param $serviceAlias
     * @return object
     * @throws \Exception
     */
    public function getBusinessRule($serviceAlias)
    {
        if (!$this->businessRules->containsKey($serviceAlias)) {
            throw new \Exception(sprintf('No business rule found for alias "%s".', $serviceAlias));
        }

        return $this->container->get($this->businessRules->get($serviceAlias));
    }

    /**
     * {@inheritdoc}
     * @return array|ArrayCollection
     */
    public function getBusinessRules()
    {
        return $this->businessRules;
    }

    /**
     * {@inheritdoc}
     * @param $service
     * @param $alias
     * @return $this
     * @throws \Exception
     */
    public function registerBusinessRule($alias, $service)
    {
        if (!$this->businessRules->containsKey($alias)) {
            $this->businessRules->set($alias, $service);
        } else {
            throw new \Exception(sprintf('Trying to redeclare business rule alias "%s".', $alias));
        }

        return $this;
    }
}
