<?php

namespace Marello\Bundle\ReturnBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\OrderBundle\Entity\Order;

class ReturnItemTypeSubscriber implements EventSubscriberInterface
{
    protected $warrantyReason = 'warranty';

    /** @var ConfigManager $configManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT       => 'onSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var ReturnItem $returnItem */
        $returnItem = $event->getData();
        $returnItem->setStatus('authorized');
        if ($returnItem->getReason() === $this->warrantyReason) {
            $warrantyValidation = $this->validateProductWarranty($returnItem);
        } else {
            $warrantyValidation = $this->validateProductRorWarranty($returnItem);
        }

        if (!$warrantyValidation) {
            $returnItem->setStatus('denied');
        }

        $event->setData($returnItem);
    }

    /**
     * @param ReturnItem $returnItem
     * @return bool
     */
    private function validateProductRorWarranty(ReturnItem $returnItem)
    {
        /** @var Shipment $shipment, tmp use order createdat until we have shipment created at */
//        $shipment       = $returnItem->getReturn()->getOrder()->getShipment();
//        $orderCreatedAt = $shipment->getCreatedAt()->format('Y-m-d');
        /** @var Order $order */
        $order          = $returnItem->getReturn()->getOrder();
        $orderCreatedAt = $order->getCreatedAt()->format('Y-m-d');
        $currentDate    = new \DateTime(date('Y-m-d'));

        /**
         * interval in days
         * @var \DateInterval $interval
         */
        $interval           = $currentDate->diff($orderCreatedAt);
        // take in account that months portion of interval cannot be greater than 12
        // so add the year into the equation
        $intervalInMonths   = ($interval->m + ($interval->y * 12));

        $systemWarrantyInMonths   = $this->configManager->get('marello_return.warranty_period');
        $productWarrantyInMonths = $returnItem->getOrderItem()->getProduct()->getWarranty();
        if (!$productWarrantyInMonths) {
            $productWarrantyInMonths = $systemWarrantyInMonths;
        }

        if ($intervalInMonths > $productWarrantyInMonths) {
            return false;
        }

        return true;
    }

    /**
     * @param ReturnItem $returnItem
     * @return bool
     */
    private function validateProductWarranty(ReturnItem $returnItem)
    {
        /** @var Shipment $shipment, tmp use order createdat until we have shipment created at */
//        $shipment       = $returnItem->getReturn()->getOrder()->getShipment();
//        $orderCreatedAt = $shipment->getCreatedAt()->format('Y-m-d');
        /** @var Order $order */
        $order          = $returnItem->getReturn()->getOrder();
        $orderCreatedAt = $order->getCreatedAt()->format('Y-m-d');
        $currentDate    = new \DateTime(date('Y-m-d'));

        $interval           = $currentDate->diff($orderCreatedAt);
        $intervalInDays     = (int) $interval->format('%a');
        $rorPeriodInDays    = $this->configManager->get('marello_return.ror_period');

        if ($intervalInDays > $rorPeriodInDays) {
            return false;
        }

        return true;
    }
}
