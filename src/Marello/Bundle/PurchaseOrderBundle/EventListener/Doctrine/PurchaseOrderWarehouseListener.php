<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class PurchaseOrderWarehouseListener
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private AclHelper $aclHelper
    ) {
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof PurchaseOrder) {
            return;
        }

        if ($entity->getWarehouse() !== null) {
            return;
        }

        $defaultWarehouse = $this->doctrine
            ->getRepository(Warehouse::class)
            ->getDefault($this->aclHelper);

        if ($defaultWarehouse === null) {
            throw new \Exception('There is no Default Warehouse');
        }

        $entity->setWarehouse($defaultWarehouse);
    }
}
