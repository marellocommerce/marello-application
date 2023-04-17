<?php

namespace Marello\Bundle\NotificationAlertBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertSourceInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertTypeInterface;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderData;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadNotificationAlertsData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const NOTIFICATION_ALERT_MESSAGE_1 = 'notification_alert_test_1';

    public function getDependencies()
    {
        return [LoadPurchaseOrderData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->getReference(LoadPurchaseOrderData::PURCHASE_ORDER_1_REF);

        $notificationAlert = new NotificationAlert();
        $notificationAlert->setMessage(self::NOTIFICATION_ALERT_MESSAGE_1);
        $notificationAlert->setSolution('Any');
        $notificationAlert->setResolved($this->getEnumValue(
            $manager,
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE,
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO
        ));
        $notificationAlert->setAlertType($this->getEnumValue(
            $manager,
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_ENUM_CODE,
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_ERROR
        ));
        $notificationAlert->setSource($this->getEnumValue(
            $manager,
            NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ENUM_CODE,
            NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_PURCHASE_ORDER
        ));
        $notificationAlert->setRelatedItemClass(PurchaseOrder::class);
        $notificationAlert->setRelatedItemId($purchaseOrder->getId());
        $notificationAlert->addActivityTarget($purchaseOrder);
        $this->setReference(self::NOTIFICATION_ALERT_MESSAGE_1. $notificationAlert);

        $manager->persist($notificationAlert);
        $manager->flush();
    }

    private function getEnumValue(ObjectManager $manager, string $code, string $id): AbstractEnumValue
    {
        $enumRepo = $manager->getRepository(ExtendHelper::buildEnumValueClassName($code));
        $enumValue = $enumRepo->find($id);
        if (!$enumValue) {
            throw new \LogicException(sprintf('Wrong enum id "%s" for "%s" enum code', $id, $code));
        }

        return $enumValue;
    }
}
