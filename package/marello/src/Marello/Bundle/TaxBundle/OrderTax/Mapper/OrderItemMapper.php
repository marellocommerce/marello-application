<?php

namespace Marello\Bundle\TaxBundle\OrderTax\Mapper;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
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
        /** @var Product $product */
        $product = $lineItem->getProduct();
        $salesChannel = $order->getSalesChannel();
        $taxable = (new Taxable())
            ->setIdentifier($lineItem->getId())
            ->setClassName($this->getProcessingClassName())
            ->setQuantity($lineItem->getQuantity())
            ->setTaxationAddress($this->getTaxationAddress($order))
            ->setTaxCode($product->getSalesChannelTaxCode($salesChannel))
            ->setPrice($lineItem->getPrice())
            ->setCurrency($lineItem->getCurrency());

        return $taxable;
    }
}
