<?php

namespace Marello\Bundle\OrderBundle\EventListener;

use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertySetEvent;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\OrderItemTypeInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\DependencyInjection\ServiceLink;

class OrderCreatedNotificationSender
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

        if ($entity instanceof Order && $this->configManager->get('marello_order.order_notification') === true) {
            $totalItemsCandC = 0;
            foreach ($entity->getItems() as $item) {
                if ($item->getItemType() === OrderItemTypeInterface::OI_TYPE_CASHANDCARRY) {
                    $totalItemsCandC++;
                }
            }
            // not all items are cash and carry, so send an email when the order is created
            if ($totalItemsCandC !== $entity->getItems()->count()) {
                $this->sendNotification($entity);
            }
        }
    }

    /**
     * @param Order $order
     */
    protected function sendNotification(Order $order)
    {
        $this->emailSendProcessorLink->getService()->sendNotification(
            'marello_order_accepted_confirmation',
            [$order->getCustomer()],
            $order
        );
    }
}
