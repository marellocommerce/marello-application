<?php

namespace Marello\Bundle\InventoryBundle\Model\InventoryBalancer;

use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\VirtualInventoryFactory;

class VirtualInventoryHandler
{
    /** @var VirtualInventoryFactory $triggerFactory */
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
     */
    public function saveVirtualInventory(VirtualInventoryLevel $level)
    {
        $repository = $this->objectManager->getRepository(VirtualInventoryLevel::class);
        $existingLevel = $repository->findOneBy(['salesChannelGroup' => $level->getSalesChannelGroup(), 'product' => $level->getProduct()]);
        if ($existingLevel) {
            $level = $existingLevel;
            $level->setInventory($level->getInventory());
        }

        $this->objectManager->persist($level);
        $this->objectManager->flush();
    }
}
