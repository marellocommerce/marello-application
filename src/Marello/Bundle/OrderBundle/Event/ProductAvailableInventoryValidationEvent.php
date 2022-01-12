<?php

namespace Marello\Bundle\OrderBundle\Event;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Contracts\EventDispatcher\Event;

class ProductAvailableInventoryValidationEvent extends Event
{
    const NAME = 'marello_product.product_available_inventory_validation';

    /**
     * @var OrderItem
     */
    private $orderItem;

    /**
     * @var bool
     */
    private $violation;

    public function __construct(OrderItem $orderItem, $violation)
    {
        $this->orderItem = $orderItem;
        $this->violation = $violation;
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return boolean
     */
    public function getViolation()
    {
        return $this->violation;
    }

    /**
     * @param boolean $violation
     * @return $this
     */
    public function setViolation($violation)
    {
        $this->violation = $violation;

        return $this;
    }
}
