<?php

namespace Marello\Bundle\OrderBundle\Form\EventListener;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrderTotalsSubscriber implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onSubmit(FormEvent $event)
    {
        /** @var Order $order */
        $order = $event->getData();

        /*
         * Reduce items to sums of prices.
         */
        $total = $tax = $grandTotal = 0;
        $order->getItems()->map(function (OrderItem $item) use (&$total, &$tax, &$grandTotal) {
            $price = ($item->getPurchasePriceIncl()) ? $item->getPurchasePriceIncl() : $item->getPrice();
            $total += ($item->getQuantity() * $price);
            $tax += $item->getTax();
            $grandTotal += $item->getRowTotalInclTax();
        });
        $grandTotal = $grandTotal + $order->getShippingAmountInclTax();
        $grandTotal = $grandTotal - $order->getDiscountAmount();

        $order
            ->setSubtotal($total)
            ->setTotalTax($tax)
            ->setGrandTotal($grandTotal);

        $event->setData($order);
    }
}
