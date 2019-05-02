<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\EntityBundle\ORM\Registry;

class PurchaseOrderWarehouseListener
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof PurchaseOrder) {
            return;
        }

        if ($entity->getWarehouse() !== null) {
            return;
        }

        $defaultWarehouse = $this->doctrine
            ->getManagerForClass(Warehouse::class)
            ->getRepository(Warehouse::class)
            ->getDefault();

        if ($defaultWarehouse === null) {
            throw new \Exception('There is no Default Warehouse');
        }

        $entity->setWarehouse($defaultWarehouse);
    }
}
