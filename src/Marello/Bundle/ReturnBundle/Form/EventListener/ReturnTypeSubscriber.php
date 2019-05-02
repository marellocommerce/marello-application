<?php

namespace Marello\Bundle\ReturnBundle\Form\EventListener;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ReturnTypeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::SUBMIT       => 'onSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var ReturnEntity $return */
        $return = $event->getData();

        $return->getOrder()
            ->getItems()
            ->map(function (OrderItem $orderItem) use ($return) {
                $return->addReturnItem(new ReturnItem($orderItem));
            });

        $event->setData($return);
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var ReturnEntity $return */
        $return = $event->getData();

        /*
         * Remove all return items with returned quantity equal to 0.
         */
        $return->getReturnItems()
            ->filter(function (ReturnItem $returnItem) {
                return !$returnItem->getQuantity();
            })
            ->map(function (ReturnItem $returnItem) use ($return) {
                $return->removeReturnItem($returnItem);
            });

        $event->setData($return);
    }
}
