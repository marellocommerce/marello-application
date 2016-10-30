<?php

namespace Marello\Bundle\RefundBundle\Form\EventListener;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CurrencySubscriber implements EventSubscriberInterface
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

        //get currency from order
        $currency = $refund->getOrder()->getCurrency();

        $refund->setCurrency($currency);

        $event->setData($refund);
    }
}
