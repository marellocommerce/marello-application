<?php

namespace Marello\Bundle\RefundBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class RefundTotalsSubscriber implements EventSubscriberInterface
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

    /**
     * {@inheritdoc}
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var Refund $refund */
        $refund = $event->getData();

        /*
         * Reduce items to sum up the new refund amount
         */
        $refundTotal = 0.00;
        $refund->getItems()->map(function (RefundItem $item) use (&$refundTotal) {
            $refundTotal += $item->getRefundAmount();
        });

        /** @var Order $order */
        $order = $refund->getOrder();

        if (round($order->getGrandTotal(), 4) < round($refundTotal, 4)) {
            throw new ValidatorException('Cannot refund more than is actually paid');
        }

        $refund->setRefundAmount($refundTotal);
        $event->setData($refund);
    }
}
