<?php

namespace Marello\Bundle\OrderBundle\Form\EventListener;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrderItemPurchasePriceSubscriber implements EventSubscriberInterface
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
        /** @var OrderItem $orderItem */
        $orderItem = $event->getData();

        $orderItem->setPurchasePriceIncl($orderItem->getPrice());

        $event->setData($orderItem);
    }
}
