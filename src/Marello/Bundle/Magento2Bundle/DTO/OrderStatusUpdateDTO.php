<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Entity\Order as MagentoOrder;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

class OrderStatusUpdateDTO
{
    /** @var MagentoOrder */
    private $magentoOrder;

    /** @var Order */
    private $marelloOrder;

    /** @var AbstractEnumValue */
    private $status;

    /**
     * @param MagentoOrder $magentoOrder
     * @param Order $marelloOrder
     * @param AbstractEnumValue $status
     */
    public function __construct(
        MagentoOrder $magentoOrder,
        Order $marelloOrder,
        AbstractEnumValue $status
    ) {
        $this->magentoOrder = $magentoOrder;
        $this->marelloOrder = $marelloOrder;
        $this->status = $status;
    }

    /**
     * @return MagentoOrder
     */
    public function getMagentoOrder(): MagentoOrder
    {
        return $this->magentoOrder;
    }

    /**
     * @return Order
     */
    public function getMarelloOrder(): Order
    {
        return $this->marelloOrder;
    }

    /**
     * @return AbstractEnumValue
     */
    public function getStatus(): AbstractEnumValue
    {
        return $this->status;
    }
}
