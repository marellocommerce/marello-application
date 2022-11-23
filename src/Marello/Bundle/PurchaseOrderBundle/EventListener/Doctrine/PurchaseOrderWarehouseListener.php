<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class PurchaseOrderWarehouseListener
{
    public function __construct(
        private Registry $doctrine,
        private AclHelper $aclHelper
    ) {
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
            ->getRepository(Warehouse::class)
            ->getDefault($this->aclHelper);

        if ($defaultWarehouse === null) {
            throw new \Exception('There is no Default Warehouse');
        }

        $entity->setWarehouse($defaultWarehouse);
    }
}
