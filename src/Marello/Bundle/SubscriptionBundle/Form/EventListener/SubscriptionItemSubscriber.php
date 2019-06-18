<?php

namespace Marello\Bundle\SubscriptionBundle\Form\EventListener;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Entity\SubscriptionItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SubscriptionItemSubscriber implements EventSubscriberInterface
{
    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var Subscription $entity */
        $entity = $event->getData();
        $form   = $event->getForm();
        /** @var Product $product */
        $product = $form->get('item')->getData();
        /** @var SalesChannel $salesChannel */
        $salesChannel = $form->get('salesChannel')->getData();
        $paymentFreq = $entity->getPaymentFreq();
        if ($paymentFreq && $paymentFreq->getId()) {
            $entity->setPaymentFreq($paymentFreq->getId());
        }
        if ($product && $salesChannel) {
            $entity
                ->setCurrency($salesChannel->getCurrency())
                ->setDuration($product->getSubscriptionDuration());
            
            $assembledPrice = $product->getSalesChannelPrice($salesChannel) ?:
                $product->getPrice($salesChannel->getCurrency());
            $defaultPrice = $assembledPrice->getDefaultPrice()->getValue();
            $specialPrice = $assembledPrice->getSpecialPrice() ? $assembledPrice->getSpecialPrice()->getValue() : null;

            $subscriptionItem = new SubscriptionItem();
            $subscriptionItem
                ->setSku($product->getSku())
                ->setDuration($product->getSubscriptionDuration())
                ->setPrice($defaultPrice)
                ->setSpecialPrice($specialPrice)
                ->setSpecialPriceDuration($product->getSpecialPriceDuration());
            $entity->setItem($subscriptionItem);
        }
    }
}
