<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadAllocationStatusData extends AbstractFixture
{
    const ALLOCATION_STATE_ENUM_CLASS = 'marello_allocation_state';
    const ALLOCATION_STATUS_ENUM_CLASS = 'marello_allocation_status';

    /** @var array */
    protected $stateData = [
        'available' => 'Available',
        'waiting' => 'Waiting for Supply',
        'alert'  => 'Alert'
    ];

    /** @var array */
    protected $statusData = [
        'on_hand' => 'On hand',
        'dropshipping'  => 'Dropshipping',
        'backorder'  => 'Backorder',
        'preorder'  => 'Pre-order',
        'could_not_allocate'  => 'Could not allocate',
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadState($manager);
        $this->loadStatus($manager);
        $manager->flush();
    }

    protected function loadState(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::ALLOCATION_STATE_ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->stateData as $id => $name) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, false, $id);
            $manager->persist($enumOption);
        }
    }

    protected function loadStatus(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::ALLOCATION_STATUS_ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->statusData as $id => $name) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, false, $id);
            $manager->persist($enumOption);
        }
    }
}
