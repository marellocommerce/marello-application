<?php

namespace Marello\Bundle\InventoryBundle\Model\InventoryBalancer;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\DependencyInjection\Configuration;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManagerInterface;
use Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategiesRegistry;
use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategyInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;

class InventoryBalancer
{
    /** @var BalancerStrategiesRegistry $balancerStrategyRegistry */
    protected $balancerStrategyRegistry;

    /** @var BalancedInventoryHandler $balancedInventoryHandler */
    protected $balancedInventoryHandler;

    /** @var InventoryItemManagerInterface $inventoryItemManager */
    protected $inventoryItemManager;

    /** @var string $balancingStrategy */
    protected $balancingStrategy = null;

    /** @var ConfigManager $configManager */
    protected $configManager;

    /**
     * @param BalancerStrategiesRegistry $balancerRegistry
     * @param InventoryItemManagerInterface $inventoryItemManager
     * @param BalancedInventoryHandler $balancedInventoryHandler
     * @param ConfigManager $configManager
     */
    public function __construct(
        BalancerStrategiesRegistry $balancerRegistry,
        InventoryItemManagerInterface $inventoryItemManager,
        BalancedInventoryHandler $balancedInventoryHandler,
        ConfigManager $configManager
    ) {
        $this->balancerStrategyRegistry = $balancerRegistry;
        $this->inventoryItemManager = $inventoryItemManager;
        $this->balancedInventoryHandler = $balancedInventoryHandler;
        $this->configManager = $configManager;
    }

    /**
     * Balance inventory for a product
     * @param Product $product
     * @param bool $isFixed
     * @param bool $flushManager
     * @throws \Exception
     */
    public function balanceInventory(Product $product, $isFixed = false, $flushManager = false)
    {
        // no status, no balancing!
        if (!$product->getStatus()) {
            return;
        }

        // check if product is enabled, if not do not (re)balance
        if (ProductStatus::DISABLED === $product->getStatus()->getName()) {
            return;
        }

        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->getInventoryItemFromProduct($product);

        if (!$inventoryItem) {
            throw new \Exception('No inventory item found');
        }

        $inventoryLevels = $inventoryItem->getInventoryLevels();

        $filteredInventoryLevels = $this->filterInventoryLevels($inventoryLevels, $isFixed);

        $sortedWhgLevels = $this->sortInventoryLevels($filteredInventoryLevels, $isFixed);
        $linkedWhgToScgs = $this->getLinkedWarehouseGroupsToSalesChannelGroups($filteredInventoryLevels, $product);

        $this->generateResult($linkedWhgToScgs, $sortedWhgLevels, $product, $flushManager);
    }

    /**
     * Set balancing strategy for inventory balancer
     * @param string $strategyIdentifier
     */
    public function setBalancingStrategy($strategyIdentifier)
    {
        $this->balancingStrategy = $strategyIdentifier;
    }

    /**
     * Filter inventory levels by warehouse type (fixed and non-fixed)
     * @param $inventoryLevels
     * @param $isFixed
     * @return ArrayCollection|InventoryItem[]
     */
    protected function filterInventoryLevels($inventoryLevels, $isFixed)
    {
        /** @var InventoryItem[]|ArrayCollection $inventoryLevels */
        $inventoryLevels = $inventoryLevels->filter(function ($level) use ($isFixed) {
            return ($this->filterInventoryLevelByWarehouseType($level, $isFixed)
                && $this->hasWarehouseChannelGroupLink($level)
            );
        });

        return $inventoryLevels;
    }

    /**
     * Sort inventory levels by warehouse group and get available inventory quantity
     * @param ArrayCollection $inventoryLevels
     * @param $isFixed
     * @return array
     */
    protected function sortInventoryLevels($inventoryLevels, $isFixed)
    {
        $sortedWhgLevels = [];
        $inventoryLevels->map(function ($level) use (&$sortedWhgLevels, $isFixed) {
            /** @var InventoryLevel $level */
            /** @var Warehouse $warehouse */
            $warehouse = $level->getWarehouse();
            /** @var WarehouseGroup $warehouseGroup */
            $warehouseGroup = $this->getGroup($warehouse);
            if (!$isFixed) {
                if (!array_key_exists($warehouseGroup->getId(), $sortedWhgLevels)) {
                    $sortedWhgLevels[$warehouseGroup->getId()] = $level->getVirtualInventoryQty();
                } else {
                    $sortedWhgLevels[$warehouseGroup->getId()] += $level->getVirtualInventoryQty();
                }
            } else {
                $sortedWhgLevels[$warehouseGroup->getId()] = $level->getVirtualInventoryQty();
            }
        });

        return $sortedWhgLevels;
    }

    /**
     * Get linked saleschannel groups by warehouse
     * @param ArrayCollection $inventoryLevels
     * @param Product $product
     * @return array
     */
    protected function getLinkedWarehouseGroupsToSalesChannelGroups($inventoryLevels, $product = null)
    {
        $linkedWhgToScgs = [];
        $inventoryLevels->map(function ($level) use (&$linkedWhgToScgs, $product) {
            /** @var InventoryLevel $level */
            /** @var Warehouse $warehouse */
            $warehouse = $level->getWarehouse();
            /** @var WarehouseGroup $warehouseGroup */
            $warehouseGroup = $this->getGroup($warehouse);
            if ($warehouseGroup) {
                $channelLink = $this->getWarehouseChannelGroupLink($warehouse);
                // trying to maintain BC
                $filteredProduct = ($product) ? $product : $level->getInventoryItem()->getProduct();
                $salesChannelGroups = $this->getApplicableSalesChannelGroups($filteredProduct, $channelLink);
                $linkedWhgToScgs[$warehouseGroup->getId()] = $salesChannelGroups;
            }
        });

        return $linkedWhgToScgs;
    }

    /**
     * Get all the SalesChannelGroups from the product where the product is being sold
     * Filtering the SCG's based on assigned SalesChannels in the product
     * @param Product $product
     * @param WarehouseChannelGroupLink $channelLink
     * @return ArrayCollection
     */
    private function getApplicableSalesChannelGroups(Product $product, WarehouseChannelGroupLink $channelLink)
    {
        $applicableGroups = new ArrayCollection();
        $channelLinkGroups = $channelLink->getSalesChannelGroups();
        $product->getChannels()->map(function ($channel) use (&$applicableGroups, $channelLinkGroups) {
            /** @var SalesChannel $channel */
            if ($channelLinkGroups->contains($channel->getGroup())) {
                if (!$applicableGroups->contains($channel->getGroup())) {
                    $applicableGroups->add($channel->getGroup());
                }
            }
        });
        return $applicableGroups;
    }

    /**
     * Check if a inventory level has a warehouse with a group link associated
     * @param InventoryLevel $level
     * @return WarehouseChannelGroupLink
     */
    protected function hasWarehouseChannelGroupLink(InventoryLevel $level)
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->getWarehouseFromInventoryLevel($level);
        return $this->getWarehouseChannelGroupLink($warehouse);
    }

    /**
     * Filter the inventory level by warehouse type and check if a warehouse
     * @param InventoryLevel $level
     * @param $isFixed
     * @return bool
     */
    protected function filterInventoryLevelByWarehouseType(InventoryLevel $level, $isFixed)
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->getWarehouseFromInventoryLevel($level);
        $warehouseType = $warehouse->getWarehouseType();

        if ($isFixed) {
            return ($warehouseType->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED);
        }

        return ($warehouseType->getName() !== WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED);
    }

    /**
     * Get warehouse channelgroup link
     * @param Warehouse $warehouse
     * @return WarehouseChannelGroupLink|null
     */
    protected function getWarehouseChannelGroupLink(Warehouse $warehouse)
    {
        /** @var WarehouseGroup $warehouseGroup */
        $warehouseGroup = $this->getGroup($warehouse);
        if ($warehouseGroup) {
            return $warehouseGroup->getWarehouseChannelGroupLink();
        }

        return null;
    }

    /**
     * Get warehouse group
     * @param Warehouse $warehouse
     * @return WarehouseGroup
     */
    protected function getGroup(Warehouse $warehouse)
    {
        return $warehouse->getGroup();
    }

    /**
     * Generate & process balancer result
     * @param array $linkedWhgToScgs
     * @param array $sortedWhgLevels
     * @param ProductInterface $product
     * @param bool $flushManager
     */
    public function generateResult($linkedWhgToScgs, $sortedWhgLevels, $product, $flushManager)
    {
        $strategy = $this->getStrategy();
        foreach ($linkedWhgToScgs as $whgId => $scgs) {
            $inventoryTotalForWhg = $sortedWhgLevels[$whgId];
            $reservedInventoryQuantity = $this->getReservedInventoryForProductScgs($product, $scgs);
            $totalInventoryToBalance = ($inventoryTotalForWhg - $reservedInventoryQuantity);

            // prevent balanced inventory below 0.
            if ($totalInventoryToBalance < 0) {
                $totalInventoryToBalance = 0;
            }

            /** @var BalancedResultObject[] $balancedResults */
            $balancedResults = $strategy->getResults($product, $scgs, $totalInventoryToBalance);
            $this->processResults($balancedResults, $product, $flushManager);
        }
    }

    /**
     * Process results by creating virtual inventory level
     * @param array $results
     * @param ProductInterface $product
     * @param $flushManager bool
     */
    private function processResults($results, $product, $flushManager)
    {
        foreach ($results as $groupId => $result) {
            $virtualLevel = $this->balancedInventoryHandler
                ->createBalancedInventoryLevel($product, $result->getGroup(), $result->getInventoryQty());
            $this->balancedInventoryHandler->saveBalancedInventory($virtualLevel, true, $flushManager);
        }
    }

    /**
     * Get reserved inventory
     * @param ProductInterface $product
     * @param SalesChannelGroup[] $scgs
     * @return int
     */
    protected function getReservedInventoryForProductScgs(ProductInterface $product, $scgs)
    {
        $reservedInventory = 0;
        foreach ($scgs as $scg) {
            $level = $this->balancedInventoryHandler->findExistingBalancedInventory($product, $scg);
            if ($level) {
                $reservedInventory += $level->getReservedInventoryQty();
            }
        }

        return $reservedInventory;
    }

    /**
     * Get Warehouse from inventoryLevel
     * @param InventoryLevel $inventoryLevel
     * @return Warehouse
     */
    protected function getWarehouseFromInventoryLevel(InventoryLevel $inventoryLevel)
    {
        return $inventoryLevel->getWarehouse();
    }

    /**
     * Get strategy from the registry
     * @return BalancerStrategyInterface|null
     */
    protected function getStrategy()
    {
        $strategy = $this->balancingStrategy;
        if (!$this->balancingStrategy) {
            $strategy = $this->configManager->get(Configuration::SYSTEM_CONFIG_PATH_BALANCE_STRATEGY);
        }

        return $this->balancerStrategyRegistry->getStrategy($strategy);
    }

    /**
     * Get InventoryItem from Product
     * @param Product $product
     * @return null|object
     */
    protected function getInventoryItemFromProduct(Product $product)
    {
        return $this->inventoryItemManager->getInventoryItem($product);
    }
}
