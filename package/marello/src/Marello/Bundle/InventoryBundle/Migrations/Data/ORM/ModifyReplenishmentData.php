<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class ModifyReplenishmentData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var array */
    protected $data = [
        [
            'oldName' => 'End of life',
            'newName' => 'Discontinued',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadReplenishmentData::class
        ];
    }
    
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('marello_inv_reple');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $invItemRepo = $manager->getRepository(InventoryItem::class);

        foreach ($this->data as $enumData) {
            $oldId = strtolower(str_replace(' ', '_', $enumData['oldName']));

            $enumValue = $enumRepo->findOneBy(['name' => $enumData['oldName']]);
            if ($enumValue) {
                $newEnumValue = $enumRepo->createEnumValue(
                    $enumData['newName'],
                    $enumValue->getPriority(),
                    $enumValue->isDefault()
                );
                $manager->persist($newEnumValue);
                $manager->remove($enumValue);
            }
            $invItems = $invItemRepo->findBy(['replenishment' => $oldId]);
            if (!empty($invItems)) {
                foreach ($invItems as $invItem) {
                    $invItem->setReplenishment($newEnumValue);
                    $manager->persist($invItem);
                }
            }
        }
        $manager->flush();
    }
}
