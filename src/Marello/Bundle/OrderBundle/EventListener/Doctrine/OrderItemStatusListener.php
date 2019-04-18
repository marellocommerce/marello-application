<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OrderItemStatusListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;
    
    /**
     * @var AvailableInventoryProvider
     */
    protected $availableInventoryProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param AvailableInventoryProvider $availableInventoryProvider
     */
    public function __construct(DoctrineHelper $doctrineHelper, AvailableInventoryProvider $availableInventoryProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->availableInventoryProvider = $availableInventoryProvider;
    }


    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof OrderItem) {
            $product = $entity->getProduct();
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $product->getInventoryItems()->first();
            $availableInventory = $this->availableInventoryProvider->getAvailableInventory(
                $product,
                $entity->getOrder()->getSalesChannel()
            );
            if ($availableInventory < $entity->getQuantity() &&
                (
                    ($inventoryItem->isBackorderAllowed() &&
                        $inventoryItem->getMaxQtyToBackorder() >= $entity->getQuantity()
                    ) || $inventoryItem->isCanPreorder()
                )
            ) {
                $entity->setStatus($this->findStatusByName(LoadOrderItemStatusData::WAITING_FOR_SUPPLY));
            } else {
                $entity->setStatus($this->findDefaultStatus());
            }
        }
        if ($entity instanceof PackingSlipItem) {
            $orderItem = $entity->getOrderItem();
            $orderItem->setStatus($entity->getStatus());
        }
    }

    /**
     * @return null|object
     */
    private function findDefaultStatus()
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrineHelper
            ->getEntityManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->findOneByDefault(true);

        if ($status) {
            return $status;
        }

        return null;
    }
    
    /**
     * @param string $name
     * @return null|object
     */
    private function findStatusByName($name)
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrineHelper
            ->getEntityManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->find($name);

        if ($status) {
            return $status;
        }

        return null;
    }
}
