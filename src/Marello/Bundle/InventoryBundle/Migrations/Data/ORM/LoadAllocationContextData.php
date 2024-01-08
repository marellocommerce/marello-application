<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\InventoryBundle\Provider\AllocationContextInterface;

class LoadAllocationContextData extends AbstractFixture implements VersionedFixtureInterface
{
    /** @var array */
    protected $reasonData = [
        [
            'id' => AllocationContextInterface::ALLOCATION_CONTEXT_ORDER,
            'name' => 'Order',
            'isDefault'=> true
        ],
        [
            'id' => AllocationContextInterface::ALLOCATION_CONTEXT_REALLOCATION,
            'name' => 'Reallocation',
            'isDefault'=> false
        ],
        [
            'id' => AllocationContextInterface::ALLOCATION_CONTEXT_CONSOLIDATION,
            'name' => 'Consolidation',
            'isDefault'=> false
        ],
        [
            'id' => AllocationContextInterface::ALLOCATION_CONTEXT_RESHIPMENT,
            'name' => 'Reshipment',
            'isDefault'=> false
        ],
        [
            'id' => AllocationContextInterface::ALLOCATION_CONTEXT_CASH_CARRY,
            'name' => 'Order Cash and Carry',
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

    /**
     * @param ObjectManager $manager
     * @return void
     */
    protected function loadReasons(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(AllocationContextInterface::ALLOCATION_CONTEXT_ENUM_CODE);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->reasonData as $data) {
            $existingEnum = $enumRepo->find($data['id']);
            if (!$existingEnum) {
                $enumOption = $enumRepo->createEnumValue($data['name'], $priority++, $data['isDefault']);
                $manager->persist($enumOption);
            }
        }
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.2';
    }
}
