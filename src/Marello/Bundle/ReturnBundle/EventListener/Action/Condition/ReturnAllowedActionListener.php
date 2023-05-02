<?php

namespace Marello\Bundle\ReturnBundle\EventListener\Action\Condition;

use Oro\Component\Action\Event\ExtendableConditionEvent;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\OrderItemStatusesInterface;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;

class ReturnAllowedActionListener
{
    /**
     * @param ExtendableConditionEvent $event
     * @return bool
     */
    public function isReturnAllowed(ExtendableConditionEvent $event): bool
    {
        /** @var Order $order */
        $order = $event->getContext()->getEntity();
        foreach ($order->getItems() as $item) {
            $status = $item->getStatus()->getId();

            if (in_array($status, $this->getStatuses(), true)) {
                return true;
            }
        }

        $event->addError('No shipped items found');

        return false;
    }

    /**
     * @return array
     */
    protected function getStatuses(): array
    {
        return [
            LoadOrderItemStatusData::DROPSHIPPING,
            OrderItemStatusesInterface::OIS_SHIPPED,
            OrderItemStatusesInterface::OIS_COMPLETE
        ];
    }
}
