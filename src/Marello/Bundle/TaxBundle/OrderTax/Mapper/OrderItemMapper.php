<?php

namespace Marello\Bundle\TaxBundle\OrderTax\Mapper;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\TaxBundle\Model\Taxable;

class OrderItemMapper extends AbstractOrderMapper
{
    /**
     * @param OrderItem $lineItem
     *
     * {@inheritdoc}
     */
    public function map($lineItem)
    {
        $order = $lineItem->getOrder();
        $salesChannel = $order->getSalesChannel();
        $taxable = (new Taxable())
            ->setIdentifier($lineItem->getId())
            ->setClassName($this->getProcessingClassName())
            ->setQuantity($lineItem->getQuantity())
            ->setTaxationAddress($this->getTaxationAddress($order))
            ->setTaxCode($lineItem->getProduct()->getSalesChannelTaxCode($salesChannel))
            ->setPrice($lineItem->getPrice())
            ->setCurrency($lineItem->getCurrency());

        return $taxable;
    }
}
