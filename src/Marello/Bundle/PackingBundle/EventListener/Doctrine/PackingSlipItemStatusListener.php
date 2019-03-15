<?php

namespace Marello\Bundle\PackingBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class PackingSlipItemStatusListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof PackingSlipItem) {
            $warehouse = $entity->getPackingSlip()->getWarehouse();
            $warehouseType = $warehouse->getWarehouseType()->getName();
            if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                foreach ($entity->getProduct()->getInventoryItems() as $inventoryItem) {
                    if ($inventoryLevel = $inventoryItem->getInventoryLevel($warehouse)) {
                        if ($inventoryLevel->getInventoryQty() >= $entity->getQuantity()) {
                            $entity->setStatus($this->findStatus(LoadOrderItemStatusData::DROPSHIPPED));
                        } else {
                            $entity->setStatus($this->findStatus(LoadOrderItemStatusData::COULD_NOT_ALLOCATE));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $status
     * @return null|object
     */
    private function findStatus($status)
    {
        $returnReasonClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrineHelper
            ->getEntityManagerForClass($returnReasonClass)
            ->getRepository($returnReasonClass)
            ->find($status);

        if ($status) {
            return $status;
        }

        return null;
    }
}