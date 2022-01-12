<?php

namespace Marello\Bundle\OrderBundle\Event;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Contracts\EventDispatcher\Event;

class OrderItemsForNotificationEvent extends Event
{
    const NAME = 'marello_order.order_items_for_notification';
    
    /**
     * @var OrderItem[]
     */
    private $orderItems;

    /**
     * @var string
     */
    private $templateName;

    public function __construct(array $orderItems, $templateName)
    {
        $this->orderItems = $orderItems;
        $this->templateName = $templateName;
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * @param OrderItem[] $orderItems
     * @return OrderItemsForNotificationEvent
     */
    public function setOrderItems($orderItems)
    {
        $this->orderItems = $orderItems;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param string $templateName
     * @return OrderItemsForNotificationEvent
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        
        return $this;
    }
}
