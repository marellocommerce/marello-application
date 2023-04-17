<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

class LoadNotificationAlertResolvedData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        [
            'id' => NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NA,
            'name' => 'N/A',
            'isDefault'=> true
        ],
        [
            'id' => NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO,
            'name' => 'No',
            'isDefault'=> false
        ],
        [
            'id' => NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_YES,
            'name' => 'Yes',
            'isDefault'=> false
        ],
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
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE
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
