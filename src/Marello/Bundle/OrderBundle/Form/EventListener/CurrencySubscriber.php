<?php

namespace Marello\Bundle\OrderBundle\Form\EventListener;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
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
        /** @var Order $order */
        $order = $event->getData();

        if ($order->getSalesChannel() instanceof SalesChannel) {
            $currency = $order->getSalesChannel()->getCurrency();

            $order->setCurrency($currency);

            $event->setData($order);
        }
    }
}
