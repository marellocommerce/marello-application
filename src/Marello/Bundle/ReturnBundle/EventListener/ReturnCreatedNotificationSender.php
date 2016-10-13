<?php

namespace Marello\Bundle\ReturnBundle\EventListener;

use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetEvent;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

class ReturnCreatedNotificationSender
{

    /** @var ServiceLink */
    protected $emailSendProcessorLink;

    /**
     * ReturnCreatedNotificationSender constructor.
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

        if ($entity instanceof ReturnEntity) {
            $this->sendNotification($entity);
        }
    }

    protected function sendNotification(ReturnEntity $returnEntity)
    {
        $this->emailSendProcessorLink->getService()->sendNotification(
            'marello_return_created',
            [$returnEntity->getOrder()->getCustomer()->getEmail()],
            $returnEntity
        );
    }
}
