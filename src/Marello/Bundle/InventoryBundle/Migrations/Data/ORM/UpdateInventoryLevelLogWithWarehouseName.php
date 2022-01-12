<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;

class UpdateInventoryLevelLogWithWarehouseName extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $existingInventoryLevelLogs = $manager
            ->getRepository(InventoryLevelLogRecord::class)
            ->findAll();

        /** @var ObjectRepository $inventoryLevelRepository */
        $inventoryLevelRepository = $manager->getRepository(InventoryLevel::class);

        /** @var InventoryLevelLogRecord $existingInventoryLevelLog */
        foreach ($existingInventoryLevelLogs as $existingInventoryLevelLog) {
            $warehouseName = $existingInventoryLevelLog->getWarehouseName();
            if (!$warehouseName) {
                /** @var InventoryLevel $inventoryLevel */
                $inventoryLevel = $inventoryLevelRepository
                    ->find($existingInventoryLevelLog->getInventoryLevel()->getId());

                if ($inventoryLevel) {
                    $existingInventoryLevelLog->setWarehouseName($inventoryLevel->getWarehouse()->getLabel());
                    $manager->persist($existingInventoryLevelLog);
                }
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
