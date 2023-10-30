<?php

namespace Marello\Bundle\NotificationMessageBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderData;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadNotificationMessagesData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const NOTIFICATION_MESSAGE_MESSAGE_1 = 'notification_message_test_1';

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

        $notificationMessage = new NotificationMessage();
        $notificationMessage->setTitle(self::NOTIFICATION_MESSAGE_MESSAGE_1);
        $notificationMessage->setMessage(self::NOTIFICATION_MESSAGE_MESSAGE_1);
        $notificationMessage->setSolution('Any');
        $notificationMessage->setResolved($this->getEnumValue(
            $manager,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO
        ));
        $notificationMessage->setAlertType($this->getEnumValue(
            $manager,
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ENUM_CODE,
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ERROR
        ));
        $notificationMessage->setSource($this->getEnumValue(
            $manager,
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ENUM_CODE,
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_PURCHASE_ORDER
        ));
        $notificationMessage->setRelatedItemClass(PurchaseOrder::class);
        $notificationMessage->setRelatedItemId($purchaseOrder->getId());

        $this->setReference(self::NOTIFICATION_MESSAGE_MESSAGE_1, $notificationMessage);

        $manager->persist($notificationMessage);
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
