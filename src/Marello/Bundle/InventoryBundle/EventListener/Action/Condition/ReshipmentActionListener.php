<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Action\Condition;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Oro\Component\Action\Event\ExtendableConditionEvent;

class ReshipmentActionListener
{
    public function isReshipmentAllowed(ExtendableConditionEvent $event): bool
    {
        /** @var Order $order */
        $order = $event->getContext()->getEntity();
        foreach ($order->getItems() as $item) {
            $status = $item->getStatus()->getId();
            $statuses = [LoadOrderItemStatusData::DROPSHIPPING, LoadOrderItemStatusData::SHIPPED];
            if (in_array($status, $statuses, true)) {
                return true;
            }
        }

        $event->addError('No shipped items found');

        return false;
    }
}
