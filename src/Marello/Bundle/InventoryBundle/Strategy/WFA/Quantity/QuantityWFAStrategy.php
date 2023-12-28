<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategyInterface;
use Marello\Bundle\InventoryBundle\Provider\AllocationExclusionInterface;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator\QtyWHCalculatorInterface;

class QuantityWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_quantity';
    const LABEL = 'marello.inventory.strategies.min_quantity';

    const EMPTY_WAREHOUSE_CODE = 'no_warehouse';
    const CNA_WAREHOUSE_CODE = 'could_not_allocate';

    /** @var AllocationExclusionInterface $exclusionProvider */
    private $exclusionProvider;

    /** @var EventDispatcherInterface $eventDispatcher */
    private $eventDispatcher;

    /**
     * @var Warehouse[]
     */
    private $linkedWarehouses = [];

    private $warehouseTypes = [];

    private $excludedWarehouses = [];

    /**
     * @param QtyWHCalculatorInterface $minQtyWHCalculator
     * @param DoctrineHelper $doctrineHelper
     * @param ConfigManager $configManager
     */
    public function __construct(
        private QtyWHCalculatorInterface $minQtyWHCalculator,
        private DoctrineHelper $doctrineHelper,
        private ConfigManager $configManager
    ) {
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
     * {@inheritdoc}
     */
    public function getWarehouseResults(Order $order, Allocation $allocation = null, array $initialResults = []): array
    {
        $productsByWh = [];
        $warehouses = [];
        $items = $this->exclusionProvider->getItems($order, $allocation);
        $itemsByProducts = [];
        $emptyWarehouse = new Warehouse();
        $emptyWarehouse->setWarehouseType(new WarehouseType('virtual'));
        $emptyWarehouse->setCode(self::EMPTY_WAREHOUSE_CODE);
        $noAllocationWarehouse = new Warehouse();
        $noAllocationWarehouse->setWarehouseType(new WarehouseType('virtual'));
        $noAllocationWarehouse->setCode(self::CNA_WAREHOUSE_CODE);
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

        foreach ($items as $key => $item) {
            if (!$item->getProduct()) {
                $errorContext = NotificationMessageContextFactory::createError(
                    NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ALLOCATION,
                    'marello.notificationmessage.allocation.no_sku.title',
                    'marello.notificationmessage.allocation.no_sku.message',
                    'marello.notificationmessage.allocation.no_sku.solution',
                    $order,
                    null,
                    'allocating',
                    null,
                    null,
                    null,
                    false
                );
                $this->eventDispatcher->dispatch(
                    new CreateNotificationMessageEvent($errorContext),
                    CreateNotificationMessageEvent::NAME
                );
                return [];
            }
            $itemsByProducts[sprintf(
                '%s_|_%s',
                $item->getProduct()->getSku(),
                $item->getId() ? : $key
            )] = $item;

            if ($item instanceof AllocationItem) {
                $inventoryItem = $item->getProduct()->getInventoryItem();
            } else {
                $inventoryItem = $item->getInventoryItem();
            }

            $productSku = $inventoryItem->getProduct()->getSku();
            $itemQtyToAllocateLeft = $item->getQuantity();
            // allocation for order of batches is not allocated correctly if more orders are open (for OoD)
            $inventoryLevels = $this->getInventoryLevelCandidates($inventoryItem, $item, $warehousesIds);
            $quantityAvailable = 0;

            if ($allocation &&
                $allocation->getState()->getId() !== AllocationStateStatusInterface::ALLOCATION_STATE_WFS
            ) {
                $this->getExcludedWarehouses($allocation);
            }

            $orderOnDemandReserved = false;
            // item is a reallocation (probably), but still check if OoD is enabled.
            if ($item instanceof AllocationItem &&
                ($inventoryItem->isOrderOnDemandAllowed() && $inventoryItem->isEnableBatchInventory())
            ) {
                $orderOnDemandReserved = true;
                $allocationItemId = $item->getOrderItem()->getId();
                $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryBatch::class);
                /** @var InventoryBatch $batch */
                $batch = $repo->findOneBy(['orderOnDemandRef' => $allocationItemId]);
                if ($batch && $batch->getQuantity() > 0) {
                    $warehouse = $batch->getInventoryLevel()->getWarehouse();
                    $warehouses[$warehouse->getCode()] = $warehouse;
                    $productsByWh[$productSku]['selected_wh'][$warehouse->getCode()] = $item->getQuantity();
                    $quantityAvailable = $batch->getQuantity();
                } else {
                    $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                    $productsByWh[$productSku]['selected_wh'][$emptyWarehouse->getCode()] = $item->getQuantity();
                    $quantityAvailable = $item->getQuantity();
                }
            }

            if (!$orderOnDemandReserved) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryLevels as $inventoryLevel) {
                    $warehouse = $inventoryLevel->getWarehouse();
                    $warehouses[$warehouse->getCode()] = $warehouse;
                    $this->warehouseTypes[$warehouse->getCode()] = $warehouse->getWarehouseType()->getName();
                    $virtualInventoryQuantity = $inventoryLevel->getVirtualInventoryQty();
                    // if an allocation has been rejected, and the quantity would still be available in this warehouse
                    // (of said allocation). Set the quantity (virtualInventoryQuantity) to 0 to make sure this warehouse
                    // will be excluded from allocating. Rejections of an allocation for a warehouse will be excluded for
                    // the next allocation round (triggered by the rejection).
                    if ($allocation && in_array($warehouse->getCode(), $this->excludedWarehouses)) {
                        $virtualInventoryQuantity = $inventoryLevel->getVirtualInventoryQty() - $item->getQuantityRejected();
                        if (($item->getQuantity() - $item->getQuantityRejected()) <= 0) {
                            $virtualInventoryQuantity = 0;
                        }
                    }

                    // custom logic for drop-ship warehouses
                    if ($this->isWarehouseEligible($item, $inventoryLevel, 'dropshipping')) {
                        $dropshipUnmanagedQuantity = $item->getQuantity();
                        $availableForDropShipping = $itemQtyToAllocateLeft;
                        if ($allocation && in_array($warehouse->getCode(), $this->excludedWarehouses)) {
                            $dropshipUnmanagedQuantity = 0;
                            $availableForDropShipping = 0;
                        }
                        $inventoryQty = $dropshipUnmanagedQuantity;
                        $inventoryCount = $availableForDropShipping;
                        if ($inventoryLevel->isManagedInventory()) {
                            $inventoryQty = $inventoryCount = $virtualInventoryQuantity;
                        }

                        $productsByWh[$productSku]['selected_wh'][$warehouse->getCode()] = $inventoryQty;
                        $quantityAvailable += $inventoryCount;
                    } else {
                        // default behaviour
                        $productsByWh[$productSku]['selected_wh'][$warehouse->getCode()] = $virtualInventoryQuantity;
                        $quantityAvailable += $virtualInventoryQuantity;
                    }
                    $itemQtyToAllocateLeft -= $virtualInventoryQuantity;
                }

                if ($this->isItemAvailable($item, $inventoryItem, 'ondemand')) {
                    $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                    $productsByWh[$productSku]['selected_wh'][$emptyWarehouse->getCode()] = $itemQtyToAllocateLeft;
                    $quantityAvailable += $item->getQuantity();
                }

                if ($this->isItemAvailable($item, $inventoryItem, 'preorder')) {
                    $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                    $productsByWh[$productSku]['selected_wh'][$emptyWarehouse->getCode()] = $itemQtyToAllocateLeft;
                    $quantityAvailable += $item->getQuantity();
                }

                if ($this->isItemAvailable($item, $inventoryItem, 'backorder')) {
                    $warehouses[$emptyWarehouse->getCode()] = $emptyWarehouse;
                    $productsByWh[$productSku]['selected_wh'][$emptyWarehouse->getCode()] = $itemQtyToAllocateLeft;
                    $quantityAvailable += $item->getQuantity();
                }
            }

            // for one reason or another, no warehouse could be found for this product
            // so add it to the no allocation warehouse to prevent errors but create an allocation with an alert state
            if (!isset($productsByWh[$productSku]['selected_wh'])) {
                $productsByWh[$productSku]['selected_wh'][$noAllocationWarehouse->getCode()] = $item->getQuantity();
            }
            $productsByWh[$inventoryItem->getProduct()->getSku()]['qtyOrdered'] = $item->getQuantity();
            $productsByWh[$inventoryItem->getProduct()->getSku()]['qtyAvailable'] = $quantityAvailable;
        }
        $possibleOptionsToFulfill = array_map(
            function ($item) {
                return $this->getOptions($item['selected_wh'], $item['qtyOrdered']);
            },
            $productsByWh
        );
        $optimizedOptions = $this->getOptimizedOptions($possibleOptionsToFulfill);
        $productsWithInventoryData = [];

        // format the data
        foreach ($optimizedOptions as $sku => $whs) {
            $data = $productsByWh[$sku];
            foreach ($whs as $wh) {
                $qty = $data['selected_wh'][$wh];
                if ($data['selected_wh'][$wh] > $data['qtyOrdered']) {
                    $qty = $data['qtyOrdered'];
                }
                $productsWithInventoryData[$sku][] = [
                    'sku' => $sku,
                    'wh' => $wh,
                    // if the quantity from the selected warehouse (that is available),
                    // we might not need all the inventory from this warehouse, but instead just the ordered quantity
                    'qty' => $qty,
                    'qtyOrdered' => $data['qtyOrdered']
                ];
            }

            if ($data['qtyOrdered'] > $data['qtyAvailable']) {
                // updated check to only add the no allocation once
                if (!$this->recordExists($productsWithInventoryData[$sku], $noAllocationWarehouse)) {
                    $productsWithInventoryData[$sku][] = [
                        'sku' => $sku,
                        'wh' => $noAllocationWarehouse->getCode(),
                        'qty' => $data['qtyOrdered'] - $data['qtyAvailable'],
                        'qtyOrdered' => $data['qtyOrdered']
                    ];
                }
            }
        }

        return $this->minQtyWHCalculator->calculate(
            $productsWithInventoryData,
            $itemsByProducts,
            $warehouses,
            $items
        );
    }

    /**
     * @param $productWithInventory
     * @param Warehouse $warehouse
     * @return bool
     */
    private function recordExists($productWithInventory, Warehouse $warehouse): bool
    {
        foreach ($productWithInventory as $product) {
            if ($product['wh'] === $warehouse->getCode()) {
                // it does exist so we break and return true;
                return true;
            }
        }
        return false;
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
            $whIdsPerOption[$sku] = array_map(
                'unserialize',
                array_unique(array_map('serialize', $whIdsPerOption[$sku]))
            );
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
                foreach (explode(',', $whs) as $wh) {
                    $whInvolved[$wh] = $wh;
                }
            }
            sort($whInvolved);
            $preResult[$optionId] = $whInvolved;
        }
        $unique = array_map('unserialize', array_unique(array_map('serialize', $preResult)));
        // sort the array with unique results by least total of warehouses,
        // less warehouses means less shipping for merchant, which are less costs theoretically we could,
        // if and when necessary, apply different sorting, for example, by distance.
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
        $uniqueWhs = [];
        foreach ($input as $v) {
            $uniqueWhs = array_merge($uniqueWhs, array_values($v));
        }
        $uniqueWhs = array_unique($uniqueWhs);
        $whOptions[] = $uniqueWhs;
        for ($i = 1; $i < count($uniqueWhs); $i++) {
            $element = array_shift($uniqueWhs);
            $uniqueWhs[] = $element;
            $whOptions[] = $uniqueWhs;
        }
        $product = [];
        $result = [];
        foreach ($whOptions as $whOption) {
            foreach ($whOption as $wh) {
                foreach ($input as $key => $values) {
                    if (array_search($wh, $values, true)) {
                        $product[$key] = $wh;
                    }
                }
            }
            $result[] = $product;
        }
        uasort($result, function ($resultA, $resultB) {
            return (count(array_unique($resultA)) <=> count(array_unique($resultB)));
        });

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
        $options = array_map('unserialize', array_unique(array_map('serialize', $options)));

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
     * @param OrderItem|AllocationItem $item
     * @return ArrayCollection
     * @throws \Exception
     */
    protected function getInventoryLevelCandidates(InventoryItem $inventoryItem, $item, array $warehouses)
    {
        // filter levels that either; have a warehouse in the linked warehouses,
        // have virtual inventory > 0 or have an external warehouse (drop-shipping)
        $filteredLevels = $inventoryItem
            ->getInventoryLevels()
            ->filter(function (InventoryLevel $inventoryLevel) use ($warehouses, $item, $inventoryItem) {
                $warehouse = $inventoryLevel->getWarehouse();
                // items that are order on demand are not a candidate for allocation by default.
                if ($item instanceof OrderItem &&
                    ($inventoryItem->isEnableBatchInventory() && $inventoryItem->isOrderOnDemandAllowed())
                ) {
                    return false;
                }

                if (in_array($warehouse->getId(), $warehouses)) {
                    if ($inventoryLevel->getVirtualInventoryQty() > 0) {
                        return true;
                    }
                    $warehouseType = $warehouse->getWarehouseType()->getName();
                    if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
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
        if ($this->getInventoryLevelSortingPriorityFromConfig() === 0) {
            $inventoryLevelIterator->uasort(function (InventoryLevel $a, InventoryLevel $b) {
                return $b->getVirtualInventoryQty() <=> $a->getVirtualInventoryQty();
            });
        }

        return new ArrayCollection(iterator_to_array($inventoryLevelIterator, false));
    }

    /**
     * @return int
     */
    protected function getInventoryLevelSortingPriorityFromConfig()
    {
        return (int) $this->configManager->get('marello_inventory.inventory_allocation_priority');
    }

    /**
     * @param OrderItem|AllocationItem $item
     * @param InventoryLevel $inventoryLevel
     * @param $type string
     * @return bool
     */
    protected function isWarehouseEligible($item, InventoryLevel $inventoryLevel, $type = 'full')
    {
        // these are basically rules, we might need to convert this into some rule based system
        // in order to have some more control over the priority of the conditions of the item
        $warehouse = $inventoryLevel->getWarehouse();
        $warehouseType = $warehouse->getWarehouseType()->getName();
        switch ($type) {
            case 'full':
                return ($inventoryLevel->getVirtualInventoryQty() >= $item->getQuantity());
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
        if ($allocation->getWarehouse() &&
            !in_array($allocation->getWarehouse()->getCode(), $this->excludedWarehouses)
        ) {
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
     * @param OrderItem|AllocationItem $item
     * @param InventoryItem $inventoryItem
     * @param null $type
     * @return bool
     */
    protected function isItemAvailable($item, InventoryItem $inventoryItem, $type = null)
    {
        switch ($type) {
            case 'ondemand':
                return ($inventoryItem->isOrderOnDemandAllowed());
            case 'preorder':
                return ($inventoryItem->isCanPreorder() &&
                    ($inventoryItem->getMaxQtyToPreorder() === null ||
                        $inventoryItem->getMaxQtyToPreorder() >= $item->getQuantity()
                    )
                );
            case 'backorder':
                return ($inventoryItem->isBackorderAllowed() &&
                    ($inventoryItem->getMaxQtyToBackorder() === null ||
                        $inventoryItem->getMaxQtyToBackorder() >= $item->getQuantity()
                    )
                );
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
            $warehouseGroupLink = $this->doctrineHelper->getEntityRepositoryForClass(WarehouseChannelGroupLink::class)
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

    /**
     * @param AllocationExclusionInterface $provider
     * @return void
     */
    public function setAllocationExclusionProvider(AllocationExclusionInterface $provider)
    {
        $this->exclusionProvider = $provider;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
