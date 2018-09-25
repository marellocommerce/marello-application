<?php

namespace Marello\Bundle\ReturnBundle\EventListener;

use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetEvent;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\DependencyInjection\ServiceLink;

class ReturnCreatedNotificationSender
{

    /**
     * @var ServiceLink
     */
    protected $emailSendProcessorLink;
    
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ServiceLink $emailSendProcessorLink
     * @param ConfigManager $configManager
     */
    public function __construct(ServiceLink $emailSendProcessorLink, ConfigManager $configManager)
    {
        $this->emailSendProcessorLink = $emailSendProcessorLink;
        $this->configManager = $configManager;
    }

    /**
     * @param DerivedPropertySetEvent $event
     */
    public function derivedPropertySet(DerivedPropertySetEvent $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof ReturnEntity &&
            $this->configManager->get('marello_return.return_notification') === true
        ) {
            $this->sendNotification($entity);
        }
    }

    /**
     * @param ReturnEntity $returnEntity
     */
    protected function sendNotification(ReturnEntity $returnEntity)
    {
        $this->emailSendProcessorLink->getService()->sendNotification(
            'marello_return_created',
            [$returnEntity->getOrder()->getCustomer()->getEmail()],
            $returnEntity
        );
    }
}
