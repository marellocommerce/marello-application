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
        return $this->entity->getOrder()->getShippingAddress();
    }

    /**
     * @return MarelloAddress | null
     */
    public function getShippingShipTo()
    {
        $warehouse = $this->entityManager->getRepository(Warehouse::class)->getDefault();

        return $warehouse->getAddress();
    }

    /**
     * @return string
     */
    public function getShippingCustomerEmail()
    {
        return $this->entity->getOrder()->getCustomer()->getEmail();
    }

    /**
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

    public function setEntity($entity)
    {
        if ($entity instanceof ReturnEntity) {
            $this->entity = $entity;
        }

        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}