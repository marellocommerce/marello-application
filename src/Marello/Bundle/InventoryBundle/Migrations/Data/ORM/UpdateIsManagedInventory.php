<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class UpdateIsManagedInventory extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $existingInventoryLevels = $manager
            ->getRepository(InventoryLevel::class)
            ->findAll();

        foreach ($existingInventoryLevels as $existingInventoryLevel) {
            $isManaged = $existingInventoryLevel->isManagedInventory();
            $warehouseType = $existingInventoryLevel->getWarehouse()->getWarehouseType()->getName();
            if (!$isManaged && $warehouseType !== WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                $existingInventoryLevel->setManagedInventory(true);
                $manager->persist($existingInventoryLevel);
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
