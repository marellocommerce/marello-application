<?php

namespace Marello\Bundle\InventoryBundle\Model\BalancedInventory;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository;
use Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryFactory;

class BalancedInventoryHandler
{
    /** @var BalancedInventoryFactory $balancedInventoryFactory */
    protected $balancedInventoryFactory;

    /** @var ManagerRegistry $doctrine */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     * @param BalancedInventoryFactory $balancedInventoryFactory
     */
    public function __construct(
        ManagerRegistry $doctrine,
        BalancedInventoryFactory $balancedInventoryFactory
    ) {
        $this->doctrine = $doctrine;
        $this->balancedInventoryFactory = $balancedInventoryFactory;
    }

    /**
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return BalancedInventoryLevel
     */
    public function createBalancedInventoryLevel(
        ProductInterface $product,
        SalesChannelGroup $group,
        $inventoryQty = 0
    ) {
        return $this->balancedInventoryFactory->create($product, $group, $inventoryQty);
    }

    /**
     * Save balanced inventory level
     * @param BalancedInventoryLevel $level
     * @param bool $force
     * @param bool $flushManager
     * @throws \Exception
     */
    public function saveBalancedInventory(BalancedInventoryLevel $level, $force = false, $flushManager = false)
    {
        /** @var BalancedInventoryLevel $existingLevel */
        $existingLevel = $this->findExistingBalancedInventory($level->getProduct(), $level->getSalesChannelGroup());
        if ($existingLevel) {
            if (!$this->isLevelChanged($existingLevel, $level) && !$force) {
                return;
            }

            $existingLevel
                ->setInventoryQty($level->getInventoryQty())
                ->setBalancedInventoryQty($level->getBalancedInventoryQty());

            $level = $existingLevel;
        }

        if (!$level->getOrganization()) {
            $organization = $this->getOrganization($level->getProduct());
            $level->setOrganization($organization);
        }

        try {
            if (!$existingLevel) {
                $this->getManagerForClass()->persist($level);
            }

            if ($flushManager) {
                $this->getManagerForClass()->flush();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Find existing BalancedInventoryLevel
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @return BalancedInventoryLevel|object
     */
    public function findExistingBalancedInventory(ProductInterface $product, SalesChannelGroup $group)
    {
        /** @var BalancedInventoryRepository $repository */
        $repository = $this->doctrine->getRepository(BalancedInventoryLevel::class);
        return $repository->findExistingBalancedInventory($product, $group);
    }

    /**
     * {@inheritdoc}
     * @param $entityClass
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    private function getManagerForClass($entityClass = BalancedInventoryLevel::class)
    {
        return $this->doctrine->getManagerForClass($entityClass);
    }

    /**
     * Check whether the existing level has changed inventory
     * @param BalancedInventoryLevel $existingLevel
     * @param BalancedInventoryLevel $level
     * @return bool
     */
    private function isLevelChanged($existingLevel, $level)
    {
        return ((float)$existingLevel->getInventoryQty() !== (float)$level->getInventoryQty());
    }

    /**
     * Get organization from entity
     * @param OrganizationAwareInterface $entity
     * @return OrganizationInterface
     */
    private function getOrganization(OrganizationAwareInterface $entity)
    {
        return $entity->getOrganization();
    }
}
