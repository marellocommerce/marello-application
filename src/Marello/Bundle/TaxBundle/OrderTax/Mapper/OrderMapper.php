<?php

namespace Marello\Bundle\TaxBundle\OrderTax\Mapper;

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
     * @var OrderLineItemMapper
     */
    protected $orderLineItemMapper;

    /**
     * {@inheritdoc}
     * @param Order $order
     */
    public function map($order)
    {
        $taxable = (new Taxable())
            ->setIdentifier($order->getId())
            ->setClassName(ClassUtils::getClass($order))
            //->setOrigin($this->addressProvider->getOriginAddress())
            ->setDestination($this->getDestinationAddress($order))
            ->setTaxationAddress($this->getTaxationAddress($order))
            //->setContext($this->getContext($order))
            ->setCurrency($order->getCurrency())
            ->setItems($this->mapLineItems($order->getItems()));//mapLineItems after getContext to preloadTaxCodes

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
     * @return \SplObjectStorage
     */
    protected function mapLineItems($lineItems)
    {
        $storage = new \SplObjectStorage();

        $lineItems
            ->map(
                function (OrderItem $item) use ($storage) {
                    $storage->attach($this->orderLineItemMapper->map($item));
                }
            );

        return $storage;
    }

    /**
     * @param TaxMapperInterface $orderLineItemMapper
     */
    public function setOrderItemMapper(TaxMapperInterface $orderLineItemMapper)
    {
        $this->orderLineItemMapper = $orderLineItemMapper;
    }
}
