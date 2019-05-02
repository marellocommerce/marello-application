<?php

namespace Marello\Bundle\OrderBundle\Form\EventListener;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrderItemPurchasePriceSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $pricesIncludeTax;

    /**
     * @param bool $pricesIncludeTax
     */
    public function __construct($pricesIncludeTax = false)
    {
        $this->pricesIncludeTax = $pricesIncludeTax;
    }

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

        if ($this->pricesIncludeTax === false) {
            $orderItem->setPurchasePriceIncl($orderItem->getPrice() + $orderItem->getTax() / $orderItem->getQuantity());
        } else {
            $orderItem->setPurchasePriceIncl($orderItem->getPrice());
        }

        $event->setData($orderItem);
    }
}
