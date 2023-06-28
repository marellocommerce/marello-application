<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

class LoadNotificationMessageTypeData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        [
            'id' => NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ERROR,
            'name' => 'Error',
            'isDefault'=> false
        ],
        [
            'id' => NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_WARNING,
            'name' => 'Warning',
            'isDefault'=> false
        ],
        [
            'id' => NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_SUCCESS,
            'name' => 'Success',
            'isDefault'=> false
        ],
        [
            'id' => NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_INFO,
            'name' => 'Info',
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
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ENUM_CODE
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
