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
    protected $entity;

    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return MarelloAddress | null
     */
    public function getShippingShipFrom()
    {
        $warehouse = $this->entityManager->getRepository(Warehouse::class)->getDefault();

        return $warehouse->getAddress();
    }

    /**
     * @return MarelloAddress | null
     */
    public function getShippingShipTo()
    {
        return $this->entity->getShippingAddress();
    }

    /**
     * @return string
     */
    public function getShippingCustomerEmail()
    {
        return $this->entity->getCustomer()->getEmail();
    }

    /**
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

    public function setEntity($entity)
    {
        if ($entity instanceof Order) {
            $this->entity = $entity;
        }

        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}