<?php

namespace MarelloEnterprise\Bundle\PurchaseOrderBundle\EventListener;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class InventoryUpdateBeforeListener
{
    /**
     * @param InventoryUpdateEvent $event
     */
    public function handleUpdateInventoryBeforeEvent(InventoryUpdateEvent $event)
    {
        $context = $event->getInventoryUpdateContext();
        $relatedEntity = $context->getRelatedEntity();
        if ($relatedEntity instanceof PurchaseOrder && $warehouse = $relatedEntity->getWarehouse()) {
            $context->setValue('warehouse', $warehouse);
        }
    }
}
