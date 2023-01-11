<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

use Marello\Bundle\OrderBundle\Model\OrderItemStatusesInterface;

class LoadOrderItemStatusData extends AbstractFixture implements VersionedFixtureInterface
{
    const ITEM_STATUS_ENUM_CLASS = 'marello_item_status';
    
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const SHIPPED = 'shipped';
    const DROPSHIPPING = 'dropshipping';
    const COULD_NOT_ALLOCATE = 'could_not_allocate';
    const WAITING_FOR_SUPPLY = 'waiting_for_supply';

    /** @var array */
    protected $data = [
        [
            'id' => OrderItemStatusesInterface::OIS_PENDING,
            'name' => 'Pending',
            'isDefaut'=> true
        ],
        [
            'id' => OrderItemStatusesInterface::OIS_PROCESSING,
            'name' => 'Processing',
            'isDefaut'=> false
        ],
        [
            'id' => OrderItemStatusesInterface::OIS_SHIPPED,
            'name' => 'Shipped',
            'isDefaut'=> false
        ],
        [
            'id' => OrderItemStatusesInterface::OIS_COMPLETE,
            'name' => 'Complete',
            'isDefaut'=> false
        ],
        [
            'id' => self::DROPSHIPPING,
            'name' => 'Dropshipping',
            'isDefaut'=> false
        ],
        [
            'id' => self::COULD_NOT_ALLOCATE,
            'name' => 'Could Not Allocate',
            'isDefaut'=> false
        ],
        [
            'id' => self::WAITING_FOR_SUPPLY,
            'name' => 'Waiting For Supply',
            'isDefaut'=> false
        ]
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(OrderItemStatusesInterface::ITEM_STATUS_ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $data) {
            $existingEnum = $enumRepo->find($data['id']);
            if (!$existingEnum) {
                $enumOption = $enumRepo->createEnumValue($data['name'], $priority++, $data['isDefault']);
                $manager->persist($enumOption);
            }
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }
}
