<?php

namespace Marello\Bundle\InventoryBundle\Model\VirtualInventory;

use Doctrine\Common\Persistence\ObjectManager;

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

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param VirtualInventoryFactory $virtualInventoryFactory
     */
    public function __construct(
        ObjectManager $objectManager,
        VirtualInventoryFactory $virtualInventoryFactory
    ) {
        $this->objectManager = $objectManager;
        $this->virtualInventoryFactory = $virtualInventoryFactory;
    }

    /**
     * @param Product $product
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return VirtualInventoryLevel
     */
    public function createVirtualInventory(Product $product, SalesChannelGroup $group, $inventoryQty)
    {
        return $this->virtualInventoryFactory->create($product, $group, $inventoryQty);
    }

    /**
     * Save virtual inventory
     * @param VirtualInventoryLevel $level
     * @param bool $force
     * @param bool $manual
     * @throws \Exception
     */
    public function saveVirtualInventory(VirtualInventoryLevel $level, $force = false, $manual = false)
    {
        $existingLevel = $this->findExistingVirtualInventory($level->getProduct(), $level->getSalesChannelGroup());
        file_put_contents(
            '/var/www/app/logs/debug.log',
            __METHOD__. " #" . __LINE__ . " ". print_r($this->isLevelChanged($existingLevel, $level), true). "\r\n",
            FILE_APPEND
        );
        if ($existingLevel) {
            if (!$this->isLevelChanged($existingLevel, $level) && !$force) {
                return;
            }

            $existingLevel->setInventory($level->getInventory());
            $level = $existingLevel;
        }

        if (!$level->getOrganization()) {
            $organization = $this->getOrganization($level->getProduct());
            $level->setOrganization($organization);
        }

        try {
            $this->objectManager->persist($level);
            file_put_contents(
                '/var/www/app/logs/debug.log',
                __METHOD__. " #" . __LINE__ . " ". print_r($level->getInventory(), true). "\r\n",
                FILE_APPEND
            );
            if ($manual) {
                $this->objectManager->flush();
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
        $repository = $this->objectManager->getRepository(VirtualInventoryLevel::class);
        return $repository->findExistingVirtualInventory($product, $group);
    }

    /**
     * Check whether the existing level has changed inventory
     * @param VirtualInventoryLevel $existingLevel
     * @param VirtualInventoryLevel $level
     * @return bool
     */
    private function isLevelChanged($existingLevel, $level)
    {
        return ((float)$existingLevel->getInventory() !== (float)$level->getInventory());
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
