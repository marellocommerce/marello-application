<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;

class OrderShippingServiceDataProvider implements ShippingServiceDataProviderInterface
{
    /** @var $entity */
    protected $entity;

    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var Warehouse $warehouse */
    protected $warehouse;

    /**
     * OrderShippingServiceDataProvider constructor.
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
        if (!$this->warehouse) {
            $this->setWarehouse($this->entityManager->getRepository(Warehouse::class)->getDefault());
        }

        return $this->warehouse->getAddress();
    }

    /**
     * {@inheritdoc}
     * @return MarelloAddress | null
     */
    public function getShippingShipTo()
    {
        return $this->entity->getShippingAddress();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getShippingCustomerEmail()
    {
        return $this->entity->getCustomer()->getEmail();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getShippingWeight()
    {
        $weight = array_reduce(
            $this->entity
                ->getItems()
                ->map(function (OrderItem $item) {
                    $weight = $item->getProduct()->getWeight();

                    return ($weight ?: 0) * $item->getQuantity();
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

        foreach ($this->entity->getItems() as $item) {
            $description .= sprintf(
                "%s, ",
                $item->getProductName()
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
        if ($entity instanceof Order) {
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
     * @return Warehouse|null
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }
}
