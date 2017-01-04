<?php

namespace Marello\Bundle\RefundBundle\Form\EventListener;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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

    public function onSubmit(FormEvent $event)
    {
        /** @var Refund $refund */
        $refund = $event->getData();

        /*
         * Reduce items to sum up the new refund amount
         */
        $refundTotal = 0;
        $refund->getItems()->map(function (RefundItem $item) use (&$refundTotal) {
            $refundTotal += $item->getRefundAmount();
        });

        $refund->setRefundAmount($refundTotal);
        $event->setData($refund);
    }
}
