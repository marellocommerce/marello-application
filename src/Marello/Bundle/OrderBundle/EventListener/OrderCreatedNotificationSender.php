<?php

namespace Marello\Bundle\OrderBundle\EventListener;

use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetEvent;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

class OrderCreatedNotificationSender
{

    /** @var ServiceLink */
    protected $emailSendProcessorLink;

    /**
     * OrderNumberGeneratorListener constructor.
     *
     * @param ServiceLink $emailSendProcessorLink
     */
    public function __construct(ServiceLink $emailSendProcessorLink)
    {
        $this->emailSendProcessorLink = $emailSendProcessorLink;
    }

    public function derivedPropertySet(DerivedPropertySetEvent $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Order) {
            $this->sendNotification($entity);
        }
    }

    protected function sendNotification(Order $order)
    {
        $this->emailSendProcessorLink->getService()->sendNotification(
            'marello_order_accepted_confirmation',
            [$order->getBillingAddress()->getEmail()],
            $order
        );
    }
}
