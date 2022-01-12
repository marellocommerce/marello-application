<?php

namespace Marello\Bundle\OrderBundle\Event;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Contracts\EventDispatcher\Event;

class OrderItemStatusUpdateEvent extends Event
{
    const NAME = 'marello_order.order_item_status_update';

    /**
     * @var OrderItem
     */
    private $orderItem;

    /**
     * @var string
     */
    private $statusName;

    /**
     * @param OrderItem $orderItem
     * @param string $statusName
     */
    public function __construct(OrderItem $orderItem, $statusName)
    {
        $this->orderItem = $orderItem;
        $this->statusName = $statusName;
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @param OrderItem $orderItem
     * @return OrderItemStatusUpdateEvent
     */
    public function setOrderItem($orderItem)
    {
        $this->orderItem = $orderItem;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return $this->statusName;
    }

    /**
     * @param string $statusName
     * @return OrderItemStatusUpdateEvent
     */
    public function setStatusName($statusName)
    {
        $this->statusName = $statusName;
        return $this;
    }
}
