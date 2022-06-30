<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadAllocationStatusData extends AbstractFixture
{
    const ALLOCATION_STATE_ENUM_CLASS = 'marello_allocation_state';
    const ALLOCATION_STATUS_ENUM_CLASS = 'marello_allocation_status';

    /** @var array */
    protected $stateData = [
        AllocationStateStatusInterface::ALLOCATION_STATE_AVAILABLE => 'Available',
        AllocationStateStatusInterface::ALLOCATION_STATE_WFS => 'Waiting for Supply',
        AllocationStateStatusInterface::ALLOCATION_STATE_ALERT  => 'Alert',
        AllocationStateStatusInterface::ALLOCATION_STATE_CLOSED => 'Closed'
    ];

    /** @var array */
    protected $statusData = [
        AllocationStateStatusInterface::ALLOCATION_STATUS_ON_HAND => 'On hand',
        AllocationStateStatusInterface::ALLOCATION_STATUS_DROPSHIP  => 'Dropshipping',
        AllocationStateStatusInterface::ALLOCATION_STATUS_BACK_ORDER  => 'Backorder',
        AllocationStateStatusInterface::ALLOCATION_STATUS_PRE_ORDER  => 'Pre-order',
        AllocationStateStatusInterface::ALLOCATION_STATUS_CNA  => 'Could not allocate',
        AllocationStateStatusInterface::ALLOCATION_STATUS_CLOSED => 'Closed'
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
