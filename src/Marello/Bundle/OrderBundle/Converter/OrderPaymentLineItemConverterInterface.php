<?php

namespace Marello\Bundle\OrderBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;

interface OrderPaymentLineItemConverterInterface
{
    /**
     * @param OrderItem[]|Collection $orderLineItems
     *
     * @return PaymentLineItemCollectionInterface|null
     */
    public function convertLineItems(Collection $orderLineItems);
}
