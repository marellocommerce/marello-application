<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadProductUnitData extends AbstractFixture
{
    const PRODUCT_UNIT_ENUM_CLASS = 'marello_product_unit';
    
    const PU_ITEM = 'item';
    const PU_BOX = 'box';
    const PU_PALLET = 'pallet';

    /** @var array */
    protected $data = [
        self::PU_ITEM => true,
        self::PU_BOX => false,
        self::PU_PALLET => false
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::PRODUCT_UNIT_ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
