<?php

namespace Marello\Bundle\InventoryBundle\Model\VirtualInventory;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\Repository\VirtualInventoryRepository;
use Marello\Bundle\InventoryBundle\Model\VirtualInventory\VirtualInventoryFactory;

class VirtualInventoryHandler
{
    /** @var VirtualInventoryFactory $virtualInventoryFactory */
    protected $virtualInventoryFactory;

    /** @var ManagerRegistry $doctrine */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     * @param VirtualInventoryFactory $virtualInventoryFactory
     */
    public function __construct(
        ManagerRegistry $doctrine,
        VirtualInventoryFactory $virtualInventoryFactory
    ) {
        $this->doctrine = $doctrine;
        $this->virtualInventoryFactory = $virtualInventoryFactory;
    }

    /**
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return VirtualInventoryLevel
     */
    public function createVirtualInventory(ProductInterface $product, SalesChannelGroup $group, $inventoryQty = 0)
    {
        return $this->virtualInventoryFactory->create($product, $group, $inventoryQty);
    }

    /**
     * Save virtual inventory
     * @param VirtualInventoryLevel $level
     * @param bool $force
     * @param bool $flushManager
     * @throws \Exception
     */
    public function saveVirtualInventory(VirtualInventoryLevel $level, $force = false, $flushManager = false)
    {
        /** @var VirtualInventoryLevel $existingLevel */
        $existingLevel = $this->findExistingVirtualInventory($level->getProduct(), $level->getSalesChannelGroup());
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
     * Find existing VirtualInventoryLevel
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @return VirtualInventoryLevel|object
     */
    public function findExistingVirtualInventory(ProductInterface $product, SalesChannelGroup $group)
    {
        /** @var VirtualInventoryRepository $repository */
        $repository = $this->doctrine->getRepository(VirtualInventoryLevel::class);
        return $repository->findExistingVirtualInventory($product, $group);
    }

    /**
     * @param $entityClass
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    private function getManagerForClass($entityClass = VirtualInventoryLevel::class)
    {
        return $this->doctrine->getManagerForClass($entityClass);
    }

    /**
     * Check whether the existing level has changed inventory
     * @param VirtualInventoryLevel $existingLevel
     * @param VirtualInventoryLevel $level
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
