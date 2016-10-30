<?php

namespace Marello\Bundle\ReturnBundle\Form\EventListener;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\OrderBundle\Entity\Order;

class ReturnItemTypeSubscriber implements EventSubscriberInterface
{
    const RETURN_ITEM_ENUM_CODE  = 'marello_return_status';

    protected $warrantyReason = 'warranty';

    /** @var ConfigManager $configManager */
    protected $configManager;

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param ConfigManager $configManager
     */
    public function __construct(
        ObjectManager $objectManager,
        ConfigManager $configManager
    ) {
        $this->objectManager = $objectManager;
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
     * {@inheritdoc}
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var ReturnItem $returnItem */
        $returnItem = $event->getData();
        if (!$returnItem->getStatus()) {
            $returnItemStatusEnum = $this->getEnumvalueById('authorized');
            $returnItem->setStatus($returnItemStatusEnum);
        }

        if (!$returnItem->getReason()) {
            return $this;
        }

        if ($returnItem->getReason()->getId() === $this->warrantyReason) {
            $warrantyValidation = $this->validateProductWarranty($returnItem);
        } else {
            $warrantyValidation = $this->validateProductRorWarranty($returnItem);
        }

        if (!$warrantyValidation) {
            $returnItemStatusEnum = $this->getEnumvalueById('denied');
            $returnItem->setStatus($returnItemStatusEnum);
        }

        $event->setData($returnItem);
    }

    /**
     * {@inheritdoc}
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
        $orderCreatedAt = $order->getCreatedAt();
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
     * {@inheritdoc}
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
        $orderCreatedAt = $order->getCreatedAt();
        $currentDate    = new \DateTime(date('Y-m-d'));

        $interval           = $currentDate->diff($orderCreatedAt);
        $intervalInDays     = (int) $interval->format('%a');
        $rorPeriodInDays    = $this->configManager->get('marello_return.ror_period');

        if ($intervalInDays > $rorPeriodInDays) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @param $enumValueId
     * @return null|object
     * @throws \Exception
     */
    private function getEnumvalueById($enumValueId)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::RETURN_ITEM_ENUM_CODE);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->objectManager->getRepository($className);
        $enumValue = $enumRepo->find($enumValueId);

        if (!$enumValue) {
            throw new \Exception(spritnf('Cannot find %s result for id %s', $className, $enumValueId));
        }

        return $enumValue;
    }
}
