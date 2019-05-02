<?php

namespace Marello\Bundle\ReturnBundle\Provider;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;

class ReturnShippingServiceDataProvider implements ShippingServiceDataProviderInterface
{
    /** @var $entity */
    protected $entity;

    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var Warehouse $warehouse */
    protected $warehouse;

    /**
     * ReturnShippingServiceDataProvider constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * @return MarelloAddress | null
     */
    public function getShippingShipFrom()
    {
        return $this->entity->getOrder()->getShippingAddress();
    }

    /**
     * {@inheritdoc}
     * @return MarelloAddress | null
     */
    public function getShippingShipTo()
    {
        if (!$this->warehouse) {
            $this->setWarehouse($this->entityManager->getRepository(Warehouse::class)->getDefault());
        }

        return $this->warehouse->getAddress();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getShippingCustomerEmail()
    {
        return $this->entity->getOrder()->getCustomer()->getEmail();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getShippingWeight()
    {
        $weight = array_reduce(
            $this->entity
                ->getReturnItems()
                ->map(function (ReturnItem $item) {
                    $weight = $item->getOrderItem()->getProduct()->getWeight();

                    return ($weight ?: 0) * $item->getOrderItem()->getQuantity();
                })
                ->toArray(),
            function ($carry, $value) {
                return $carry + $value;
            },
            0
        );

        return $weight;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getShippingDescription()
    {
        $description = '';

        foreach ($this->entity->getReturnItems() as $item) {
            $description .= sprintf(
                "%s, ",
                $item->getOrderItem()->getProductName()
            );
        }

        return rtrim($description, ', ');
    }

    /**
     * {@inheritdoc}
     * @param $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        if ($entity instanceof ReturnEntity) {
            $this->entity = $entity;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * {@inheritdoc}
     * @param Warehouse $warehouse
     * @return $this
     */
    public function setWarehouse(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }
}
