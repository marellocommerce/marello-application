<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\QtyWHCalculatorInterface;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategyInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class QuantityWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_quantity';
    const LABEL = 'marello.inventory.strategies.min_quantity';

    /**
     * @var bool
     */
    private $estimation = false;

    /**
     * @var QtyWHCalculatorInterface
     */
    private $minQtyWHCalculator;

    /**
     * @var WarehouseChannelGroupLinkRepository
     */
    private $warehouseChannelGroupLinkRepository;

    /** @var ConfigManager $configManager */
    private $configManager;

    /**
     * @var Warehouse[]
     */
    private $linkedWarehouses = [];

    private $warehouseTypes = [];

    private $excludedWarehouses = [];

    /**
     * @param QtyWHCalculatorInterface $minQtyWHCalculator
     * @param WarehouseChannelGroupLinkRepository $warehouseChannelGroupLinkRepository
     * @param ConfigManager $configManager
     */
    public function __construct(
        QtyWHCalculatorInterface $minQtyWHCalculator,
        WarehouseChannelGroupLinkRepository $warehouseChannelGroupLinkRepository,
        ConfigManager $configManager
    ) {
        $this->minQtyWHCalculator = $minQtyWHCalculator;
        $this->warehouseChannelGroupLinkRepository = $warehouseChannelGroupLinkRepository;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return self::LABEL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setEstimation($estimation = false)
    {
        $this->estimation = $estimation;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouseResults(Order $order, Allocation $allocation = null, array $initialResults = []): array
    {
        $productsByWh = [];
        $warehouses = [];
        $orderItems = ($allocation) ? $allocation->getItems() : $order->getItems();
        $orderItemsByProducts = [];
        $emptyWarehouse = new Warehouse();
        $emptyWarehouse->setWarehouseType(new WarehouseType('virtual'));
        $emptyWarehouse->setCode('no_warehouse');
        $noAllocationWarehouse = new Warehouse();
        $noAllocationWarehouse->setWarehouseType(new WarehouseType('virtual'));
        $noAllocationWarehouse->setCode('could_not_allocate');
        $warehouses[$noAllocationWarehouse->getCode()] = $noAllocationWarehouse;

        // the SalesChannel that the order is placed in is linked to a SalesChannelGroup
        // linked warehouses are warehouses connected to the WarehouseGroup that is linked to the SalesChannelGroup
        $linkedWarehouses = $this->getLinkedWarehouses($order);
        if (empty($linkedWarehouses)) {
            return [];
        }

        $warehousesIds = array_map(function (Warehouse $warehouse) {
            return $warehouse->getId();
        }, $linkedWarehouses);

        foreach ($orderItems as $key => $orderItem) {
            $orderItemsByProducts[sprintf(
                '%s_|_%s',
                $orderItem->getProduct()->getSku(),
                $orderItem->getId() ? : $key
            )] = $orderItem;

            /** @var ArrayCollection $inventoryItems */
            if ($orderItem instanceof AllocationItem) {
                $inventoryItems = $orderItem->getProduct()->getInventoryItems();
            } else {
                $inventoryItems = $orderItem->getInventoryItems();
            }
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $inventoryItems->first();
            $orderItemQtyToAllocateLeft = $orderItem->getQuantity();
            $inventoryLevels = $this->getInventoryLevelCandidates($inventoryItem, $warehousesIds);
            $quantityAvailable = 0;

            if ($allocation) {
                $this->getExcludedWarehouses($allocation);
            }

            /** @var InventoryLevel $inventoryLevel */
            foreach ($inventoryLevels as $i => $inventoryLevel) {
                $warehouse = $inventoryLevel->getWarehouse();
                $warehouses[$warehouse->getCode()] = $warehouse;
                $this->warehouseTypes[$warehouse->getCode()] = $warehouse->getWarehouseType()->getName();
                $virtualInventoryQuantity = $inventoryLevel->getVirtualInventoryQty();
                // if an allocation has been rejected, and the quantity would still be available in this warehouse (of said allocation)
                // set the quantity (virtualInventoryQuantity to 0 to make sure this warehouse will be excluded from allocating
                // rejections of an allocation for a warehouse will be excluded for the next allocation round (triggered by the rejection)
                if ($allocation && in_array($warehouse->getCode(), $this->excludedWarehouses)) {
                    $virtualInventoryQuantity = $inventoryLevel->getVirtualInventoryQty() - $orderItem->getQuantityRejected();
                    if (($orderItem->getQuantity() - $orderItem->getQuantityRejected()) <= 0) {
                        $virtualInventoryQuantity = 0;
                    }
                }

                // custom logic for dropship warehouses
                if ($this->isWarehouseEligible($orderItem, $inventoryLevel, 'dropshipping')) {
                    $dropshipUnmanagedQuantity = $orderItem->getQuantity();
                    $availableForDropShipping = $orderItemQtyToAllocateLeft;
                    if ($allocation && in_array($warehouse->getCode(), $this->excludedWarehouses)) {
                        $dropshipUnmanagedQuantity = 0;
                        $availableForDropShipping = 0;
                    }
                    $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$warehouse->getCode()] = ($inventoryLevel->isManagedInventory()) ? $virtualInventoryQuantity : $dropshipUnmanagedQuantity;
                    $quantityAvailable += ($inventoryLevel->isManagedInventory()) ? $virtualInventoryQuantity : $availableForDropShipping;
                } else {
                    // default behaviour
                    $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$warehouse->getCode()] = $virtualInventoryQuantity;
                    $quantityAvailable += $virtualInventoryQuantity;
                }
                $orderItemQtyToAllocateLeft -= $virtualInventoryQuantity;
            }

            if ($this->isItemAvailable($orderItem, $inventoryItem, 'ondemand')) {
                $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$emptyWarehouse->getCode()] = $orderItemQtyToAllocateLeft;
                $quantityAvailable += $orderItem->getQuantity();
            }

            if ($this->isItemAvailable($orderItem, $inventoryItem, 'preorder')) {
                $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$emptyWarehouse->getCode()] = $orderItemQtyToAllocateLeft;
                $quantityAvailable += $orderItem->getQuantity();
            }

            if ($this->isItemAvailable($orderItem, $inventoryItem, 'backorder')) {
                $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$emptyWarehouse->getCode()] = $orderItemQtyToAllocateLeft;
                $quantityAvailable += $orderItem->getQuantity();
            }

            // for one reason or another, no warehouse could be found for this product
            // so add it to the no allocation warehouse to prevent errors but create an allocation with an alert state
            if (!isset($productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'])) {
                $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$noAllocationWarehouse->getCode()] = $orderItem->getQuantity();
            }
            $productsByWh[$inventoryItem->getProduct()->getSku()]['qtyOrdered'] = $orderItem->getQuantity();
            $productsByWh[$inventoryItem->getProduct()->getSku()]['qtyAvailable'] = $quantityAvailable;
        }

        $possibleOptionsToFulfill = array_map(function($item) {
                return $this->getOptions($item['selected_wh'], $item['qtyOrdered']);
            }, $productsByWh
        );
        $optimizedOptions = $this->getOptimizedOptions($possibleOptionsToFulfill);
        $productsWithInventoryData = [];

        // format the data
        foreach ($optimizedOptions as $sku => $whs) {
            $data = $productsByWh[$sku];
            foreach ($whs as $wh) {
                $productsWithInventoryData[$sku][] = [
                    'sku' => $sku,
                    'wh' => $wh,
                    // if the quantity from the selected warehouse (that is available), we might not need all the inventory
                    // from this warehouse, but instead just the ordered quantity
                    'qty' => ($data['selected_wh'][$wh] > $data['qtyOrdered']) ? $data['qtyOrdered']: $data['selected_wh'][$wh],
                    'qtyOrdered' => $data['qtyOrdered']
                ];
            }

            if ($data['qtyOrdered'] > $data['qtyAvailable']) {
                if (!isset($productsWithInventoryData[$sku])) {
                    $productsWithInventoryData[$sku][] = [
                        'sku' => $sku,
                        'wh' => $noAllocationWarehouse->getCode(),
                        'qty' => $data['qtyOrdered'] - $data['qtyAvailable'],
                        'qtyOrdered' => $data['qtyOrdered']
                    ];
                }
            }
        }

        return $this->minQtyWHCalculator->calculate($productsWithInventoryData, $orderItemsByProducts, $warehouses, $orderItems);
    }

    /**
     * @param $allFoundOptions
     * @return array
     */
    protected function getOptimizedOptions($allFoundOptions): array
    {
        $whIdsPerOption = [];
        // per product, get unique combinations of warehouses involved
        foreach ($allFoundOptions as $sku => $options) {
            foreach ($options as $optionId => $option) {
                $whIdsPerOption[$sku][$optionId] = implode(',', array_keys($option));
            }

            // remove duplicates
            $whIdsPerOption[$sku] = array_map("unserialize", array_unique(array_map("serialize", $whIdsPerOption[$sku])));
            // sort the options within (lowest to the highest nr of warehouses)
            $optionsOrder = [];
            foreach ($whIdsPerOption[$sku] as $optionId => $warehouses) {
                $optionsOrder[$optionId] = count(explode(',', $warehouses));
            }
            asort($optionsOrder); // start with options with the least amount of warehouses

            // sort the warehouses array accordingly
            $whIdsPerOption[$sku] = array_merge(array_flip(array_keys($optionsOrder)), $whIdsPerOption[$sku]);
        }

        // sort the products within the order (lowest to the highest nr of options)
        $productOrder = [];
        foreach ($whIdsPerOption as $sku => $options) {
            $productOrder[$sku] = count($options);
        }
        asort($productOrder); // start with orders with the least amount of options

        // sort the options array accordingly
        $whIdsPerOption = array_replace(array_flip(array_keys($productOrder)), $whIdsPerOption);
        // get all possible combinations of warehouses
        $cartesianResult = $this->cartesian($whIdsPerOption);

        $preResult = [];
        foreach ($cartesianResult as $optionId => $products) {
            $whInvolved = [];
            foreach ($products as $whs) {
                foreach (explode(',',$whs) as $wh) {
                    $whInvolved[$wh] = $wh;
                }
            }
            sort($whInvolved);
            $preResult[$optionId] = $whInvolved;
        }
        $unique = array_map("unserialize", array_unique(array_map("serialize", $preResult)));
        // sort the array with unique results by least total of warehouses, less warehouses means less shipping for merchant, which are less costs theoretically
        // we could, if and when necessary, apply different sorting, for example, by distance.
        // it does however, need some modification as we do not have all data present for different sorting.
        uasort($unique, function ($resultA, $resultB) {
            $typeWeightA = $this->getTypeWeight($resultA);
            $typeWeightB = $this->getTypeWeight($resultB);
            $typePriority = 100;
            // count is done ASC, aka less allocations and therefore less shipments
            // type is done DESC, as we want to influence the order based on config setting
            return
                (count($resultA) <=> count($resultB)) +
                ($typeWeightB <=> $typeWeightA) * $typePriority;
        });

        // first item in the array is option with the least total of warehouses.
        $firstResult = array_shift($unique);

        // find the correct warehouse per product after the ideal result for warehouses have been found,
        // this necessary as we need the warehouses per product in order to generate the OrderWarehouseResult.
        return array_map(function ($item) use ($firstResult) {
            if (count($item) === 1) {
                // we just need the first result
                return explode(',', array_shift($item));
            }
            // filter the options whether the warehouse from 'all options' are in the result of the optimized option
            // if not, we can't use that option.
            $filterOptions = array_filter($item, function ($option) use ($firstResult, $item) {
                $explodedWhs = explode(',', $option);
                $isValidOption = true;
                foreach ($explodedWhs as $wh) {
                    if (!in_array($wh, $firstResult)) {
                        $isValidOption = false;
                    }
                }
                return $isValidOption;
            });
            // could be that there is more than one result and we just need the first result
            return explode(',', array_shift($filterOptions));
        }, $whIdsPerOption);
    }

    /**
     * Get weight for warehouses to determine comparison which result should be used first
     * @param array $result
     * @param int $typeWeight
     * @return int
     */
    protected function getTypeWeight(array $result, $typeWeight = 10): int
    {
        // mixed --> priority is based on available inventory, but external options should be pushed down
        if ($this->getInventoryLevelSortingPriorityFromConfig() === 0) {
            foreach ($result as $code) {
                if ($this->warehouseTypes[$code] === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                    $typeWeight -= 100;
                }
            }
            return $typeWeight;
        }

        // internal --> internal options have priority above internal
        if ($this->getInventoryLevelSortingPriorityFromConfig() === 1) {
            foreach ($result as $code) {
                if ($this->warehouseTypes[$code] === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                    $typeWeight -= 1000;
                }
            }
        }

        // external --> external options have priority above internal
        if ($this->getInventoryLevelSortingPriorityFromConfig() === 2) {
            foreach ($result as $code) {
                if ($this->warehouseTypes[$code] === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                    $typeWeight = 1000;
                }
            }
        }

        return $typeWeight;
    }

    /**
     * see: https://stackoverflow.com/questions/6311779/finding-cartesian-product-with-php-associative-arrays
     * @param $input
     * @return array
     */
    private function cartesian($input): array
    {
        $result = array(array());
        foreach ($input as $key => $values) {
            $append = array();

            foreach($result as $product) {
                foreach($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }

    /**
     * getOptions
     * @param $selectedWhs
     * @param $qtyOrdered
     * @return array
     */
    private function getOptions($selectedWhs, $qtyOrdered): array
    {
        $options = [];

        // get all permutations of ids of warehouse (with available stock)
        $id = 0;
        foreach ($this->permutations(array_keys($selectedWhs)) as $permutation) {
            $processedPermutation = $this->processPermutation($permutation, $selectedWhs, $qtyOrdered);
            ksort($processedPermutation); // sort them
            $options["OP$id"] = $processedPermutation;
            $id++;
        }

        // remove duplicates
        $options = array_map("unserialize", array_unique(array_map("serialize", $options)));

        return $options;
    }

    /**
     * @param $permutation // combination of warehouse ids
     * @param $selectedWhs
     * @param $qtyOrdered
     * @return array
     */
    private function processPermutation($permutation, $selectedWhs, $qtyOrdered): array
    {
        $result = [];
        $qtyRemain = $qtyOrdered;
        foreach ($permutation as $whId) {
            $stock = $selectedWhs[$whId];
            if ($stock > 0) {
                $result[$whId] = min($qtyRemain, $stock);
                $qtyRemain -= $stock;
            }
            // if all ordered qty is fulfilled, stop
            if ($qtyRemain <= 0) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param array $elements
     * @return \Generator
     */
    private function permutations(array $elements): \Generator
    {
        if (count($elements) <= 1) {
            yield $elements;
        } else {
            foreach ($this->permutations(array_slice($elements, 1)) as $permutation) {
                foreach (range(0, count($elements) - 1) as $i) {
                    yield array_merge(
                        array_slice($permutation, 0, $i),
                        [$elements[0]],
                        array_slice($permutation, $i)
                    );
                }
            }
        }
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param array $warehouses
     * @return ArrayCollection
     * @throws \Exception
     */
    protected function getInventoryLevelCandidates(InventoryItem $inventoryItem, array $warehouses)
    {
        // filter levels that either; have an warehouse in the linked warehouses, have virtual inventory > 0 or have an external warehouse (dropshipping)
        $filteredLevels = $inventoryItem
            ->getInventoryLevels()
            ->filter(function(InventoryLevel $inventoryLevel) use ($warehouses) {
                if (in_array($inventoryLevel->getWarehouse()->getId(), $warehouses)) {
                    if ($inventoryLevel->getVirtualInventoryQty() > 0) {
                        return true;
                    }
                    if ($inventoryLevel->getWarehouse()->getWarehouseType()->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                        if (($inventoryLevel->isManagedInventory() && $inventoryLevel->getVirtualInventoryQty() > 0)
                            || !$inventoryLevel->isManagedInventory()
                        ) {
                            return true;
                        }
                    }
                }

                return false;
            });

        $inventoryLevelIterator = $filteredLevels->getIterator();
        // mixed --> still relevant?
        if ($this->getInventoryLevelSortingPriorityFromConfig() === 0) {
            $inventoryLevelIterator->uasort(function(InventoryLevel $a, InventoryLevel $b) {
                return $b->getVirtualInventoryQty() <=> $a->getVirtualInventoryQty();
            });
        }

        return new ArrayCollection(iterator_to_array($inventoryLevelIterator, false));
    }

    protected function getInventoryLevelSortingPriorityFromConfig()
    {
        return (int) $this->configManager->get('marello_inventory.inventory_allocation_priority');
    }

    /**
     * @param OrderItem|AllocationItem $orderItem
     * @param InventoryLevel $inventoryLevel
     * @param $type string
     * @return bool
     */
    protected function isWarehouseEligible($orderItem, InventoryLevel $inventoryLevel, $type = 'full')
    {
        // these are basically rules, we might need to convert this into some rule based system
        // in order to have some more control over the priority of the conditions of the item
        $warehouse = $inventoryLevel->getWarehouse();
        $warehouseType = $warehouse->getWarehouseType()->getName();
        switch ($type){
            case 'full':
                return ($inventoryLevel->getVirtualInventoryQty() >= $orderItem->getQuantity());
                break;
            case 'dropshipping':
                return ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Build up a list of previous warehouses that were excluded and should not be taken into account to
     * allocate the product to.
     * @param Allocation $allocation
     */
    protected function getExcludedWarehouses(Allocation $allocation)
    {
        if ($allocation->getWarehouse() && !in_array($allocation->getWarehouse()->getCode(), $this->excludedWarehouses)) {
            $this->excludedWarehouses[] = $allocation->getWarehouse()->getCode();
        }

        // get allocation source (from a previous rejection) until no source from rejection has been found
        // this will create a list of excluded warehouses it should not try to allocate to again
        // as the allocation was already once rejected by them
        if ($allocation->getSourceEntity()) {
            $this->getExcludedWarehouses($allocation->getSourceEntity());
        }
    }

    /**
     * @param OrderItem|AllocationItem $orderItem
     * @param InventoryItem $inventoryItem
     * @param null $type
     * @return bool
     */
    protected function isItemAvailable($orderItem, InventoryItem $inventoryItem, $type = null)
    {
        switch ($type) {
            case 'ondemand':
                return ($inventoryItem->isOrderOnDemandAllowed());
            case 'preorder':
                return ($inventoryItem->isCanPreorder() &&
                    ($inventoryItem->getMaxQtyToPreorder() === null || $inventoryItem->getMaxQtyToPreorder() >= $orderItem->getQuantity()));
            case 'backorder':
                return ($inventoryItem->isBackorderAllowed() &&
                    ($inventoryItem->getMaxQtyToBackorder() === null || $inventoryItem->getMaxQtyToBackorder() >= $orderItem->getQuantity()));
            default:
                return false;
        }
    }

    /**
     * @param Order $order
     * @return Warehouse[]
     */
    private function getLinkedWarehouses(Order $order)
    {
        if (empty($this->linkedWarehouses)) {
            if (!$order->getSalesChannel() || !$order->getSalesChannel()->getGroup()) {
                return [];
            }
            /** @var WarehouseChannelGroupLink $warehouseGroupLink */
            $warehouseGroupLink = $this->warehouseChannelGroupLinkRepository
                ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup());

            if (!$warehouseGroupLink) {
                return [];
            }

            /** @var Warehouse[] $linkedWarehouses */
            $linkedWarehouses = $warehouseGroupLink
                ->getWarehouseGroup()
                ->getWarehouses()
                ->toArray();

            $this->linkedWarehouses = $linkedWarehouses;
        }

        return $this->linkedWarehouses;
    }
}
