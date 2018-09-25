<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\EventListener;

use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;

class ChangeSalesChannelGroupSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::SUBMIT => ['onSubmit', 10]];
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var WarehouseChannelGroupLink $link */
        $link = $event->getData();

        if (!$link) {
            return;
        }

        $form = $event->getForm();

        /** @var SalesChannelGroup $channelGroup */
        foreach ($form->get('addSalesChannelGroups')->getData() as $channelGroup) {
            $link->addSalesChannelGroup($channelGroup);
        }

        /** @var SalesChannelGroup $channelGroup */
        foreach ($form->get('removeSalesChannelGroups')->getData() as $channelGroup) {
            $link->removeSalesChannelGroup($channelGroup);
        }
    }
}
