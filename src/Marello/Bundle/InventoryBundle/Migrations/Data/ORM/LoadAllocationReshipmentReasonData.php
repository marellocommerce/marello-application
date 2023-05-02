<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Marello\Bundle\InventoryBundle\Provider\AllocationReshipmentReasonInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

class LoadAllocationReshipmentReasonData extends AbstractFixture implements VersionedFixtureInterface
{
    /** @var array */
    protected $data = [
        [
            'id' => AllocationReshipmentReasonInterface::ALLOCATION_RESHIPMENT_REASON_LOST,
            'name' => 'Package lost in transit',
            'isDefault'=> true
        ],
        [
            'id' => AllocationReshipmentReasonInterface::ALLOCATION_RESHIPMENT_REASON_WRONG_ITEM,
            'name' => 'Wrong item delivered',
            'isDefault'=> false
        ],
        [
            'id' => AllocationReshipmentReasonInterface::ALLOCATION_RESHIPMENT_REASON_DAMAGED,
            'name' => 'Package/Product damaged',
            'isDefault'=> false
        ],
        [
            'id' => AllocationReshipmentReasonInterface::ALLOCATION_RESHIPMENT_REASON_OTHER,
            'name' => 'Other',
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
        $className = ExtendHelper::buildEnumValueClassName(AllocationReshipmentReasonInterface::ALLOCATION_RESHIPMENT_REASON_ENUM_CODE);

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

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }
}
