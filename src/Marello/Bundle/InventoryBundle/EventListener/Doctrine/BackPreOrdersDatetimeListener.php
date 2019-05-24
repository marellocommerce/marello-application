<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;

class BackPreOrdersDatetimeListener implements FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param LifecycleEventArgs $args
     */
    public function postPersist(PurchaseOrder $purchaseOrder, LifecycleEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        if ($purchaseOrder->getDueDate() &&
            $this->featureChecker->isFeatureEnabled('po_duedate_as_back_orders_datetime')) {
            foreach ($purchaseOrder->getItems() as $item) {
                if ($product = $item->getProduct()) {
                    /** @var InventoryItem $inventoryItem */
                    $inventoryItem = $product->getInventoryItems()->first();
                    $inventoryItem->setBackOrdersDatetime($purchaseOrder->getDueDate());
                    $uow->scheduleForUpdate($inventoryItem);
                }
            }
        }
    }
}
