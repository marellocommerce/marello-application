<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertSourceInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

class LoadNotificationAlertSourceData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        [
            'id' => NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ORDER,
            'name' => 'Order',
            'isDefault'=> false
        ],
        [
            'id' => NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_PURCHASE_ORDER,
            'name' => 'Purchase Order',
            'isDefault'=> false
        ],
        [
            'id' => NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ALLOCATION,
            'name' => 'Allocation',
            'isDefault'=> false
        ],
        [
            'id' => NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_WEBHOOK,
            'name' => 'Webhook',
            'isDefault'=> false
        ]
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadReasons($manager);
        $manager->flush();
    }

    protected function loadReasons(ObjectManager $manager): void
    {
        $className = ExtendHelper::buildEnumValueClassName(
            NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ENUM_CODE
        );

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $priority = 1;
        foreach ($this->data as $item) {
            $existingEnum = $enumRepo->find($item['id']);
            if (!$existingEnum) {
                $enumOption = $enumRepo->createEnumValue($item['name'], $priority++, $item['isDefault'], $item['id']);
                $manager->persist($enumOption);
            }
        }
    }
}
