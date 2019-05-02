<?php

namespace Marello\Bundle\OrderBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;

interface OrderShippingLineItemConverterInterface
{
    /**
     * @param OrderItem[]|Collection $orderLineItems
     *
     * @return ShippingLineItemCollectionInterface|null
     */
    public function convertLineItems(Collection $orderLineItems);
}
