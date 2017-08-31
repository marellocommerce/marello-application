<?php

namespace Marello\Bundle\TaxBundle\OrderTax\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\TaxBundle\Mapper\TaxMapperInterface;
use Marello\Bundle\TaxBundle\Model\Taxable;

class OrderMapper extends AbstractOrderMapper
{
    /**
     * @var OrderItemMapper
     */
    protected $orderItemMapper;

    /**
     * {@inheritdoc}
     * @param Order $order
     */
    public function map($order)
    {
        $taxable = (new Taxable())
            ->setIdentifier($order->getId())
            ->setClassName(ClassUtils::getClass($order))
            ->setTaxationAddress($this->getTaxationAddress($order))
            ->setCurrency($order->getCurrency())
            ->setItems($this->mapLineItems($order->getItems()));

        if ($order->getSubtotal()) {
            $taxable->setAmount($order->getSubtotal());
        }

        if ($order->getShippingAmountInclTax()) {
            $taxable->setShippingCost($order->getShippingAmountInclTax());
        }

        return $taxable;
    }

    /**
     * @param Selectable|Collection|OrderItem[] $lineItems
     * @return Collection
     */
    protected function mapLineItems($lineItems)
    {
        $storage = new ArrayCollection();

        $lineItems
            ->map(
                function (OrderItem $item) use ($storage) {
                    if ($item->getProduct()) {
                        $storage->add($this->orderItemMapper->map($item));
                    }
                }
            );

        return $storage;
    }

    /**
     * @param TaxMapperInterface $orderItemMapper
     */
    public function setOrderItemMapper(TaxMapperInterface $orderItemMapper)
    {
        $this->orderItemMapper = $orderItemMapper;
    }
}
