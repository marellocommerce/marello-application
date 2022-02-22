<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity;

use Doctrine\Common\Collections\ArrayCollection;
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
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;

class MinimumQuantityWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_quantity';
    const LABEL = 'marelloenterprise.inventory.strategies.min_quantity';

    /**
     * @var bool
     */
    private $estimation = false;

    /**
     * @var MinQtyWHCalculatorInterface
     */
    private $minQtyWHCalculator;

    /**
     * @var WarehouseChannelGroupLinkRepository
     */
    private $warehouseChannelGroupLinkRepository;

    /**
     * @var Warehouse[]
     */
    private $linkedWarehouses = [];

    /**
     * @param MinQtyWHCalculatorInterface $minQtyWHCalculator
     * @param WarehouseChannelGroupLinkRepository $warehouseChannelGroupLinkRepository
     */
    public function __construct(
        MinQtyWHCalculatorInterface $minQtyWHCalculator,
        WarehouseChannelGroupLinkRepository $warehouseChannelGroupLinkRepository
    ) {
        $this->minQtyWHCalculator = $minQtyWHCalculator;
        $this->warehouseChannelGroupLinkRepository = $warehouseChannelGroupLinkRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return self::LABEL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
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
    public function getWarehouseResults(Order $order, array $initialResults = [])
    {
        $productsByWh = [];
        $warehouses = [];
        $orderItems = $order->getItems();
        $orderItemsByProducts = [];
        $emptyWarehouse = new Warehouse();
        $emptyWarehouse->setWarehouseType(new WarehouseType('virtual'));
        $emptyWarehouse->setCode('no_warehouse');

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
            $inventoryItems = $orderItem->getInventoryItems();
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $inventoryItems->first();
            $orderItemQtyToAllocateLeft = $orderItem->getQuantity();
            $inventoryLevels = $this->getInventoryLevelCandidates($inventoryItem, $warehousesIds);
            $quantityAvailable = 0;
            /** @var InventoryLevel $inventoryLevel */
            foreach ($inventoryLevels as $i => $inventoryLevel) {
                $warehouse = $inventoryLevel->getWarehouse();
                $warehouses[$warehouse->getCode()] = $warehouse;
//                        if ($orderItemQtyToAllocateLeft > 0) {
                $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$warehouse->getCode()] = $inventoryLevel->getVirtualInventoryQty();
                            //$this->getAllocatedQty($inventoryLevel, $orderItem, $orderItemQtyToAllocateLeft, $i);
//                        }
                $quantityAvailable += $inventoryLevel->getVirtualInventoryQty();
                if ($this->isWarehouseEligible($orderItem, $inventoryLevel, 'dropshipping')) {
                    $productsByWh[$inventoryItem->getProduct()->getSku()]['selected_wh'][$warehouse->getCode()] = $orderItemQtyToAllocateLeft;
                    $quantityAvailable += $inventoryLevel->getVirtualInventoryQty();
                }
                $orderItemQtyToAllocateLeft -= $inventoryLevel->getVirtualInventoryQty();
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
            $productsByWh[$inventoryItem->getProduct()->getSku()]['qtyOrdered'] = $orderItem->getQuantity();
            $productsByWh[$inventoryItem->getProduct()->getSku()]['qtyAvailable'] = $quantityAvailable;
        }

        $possibleOptionsToFulfill = array_map(function($item) {
                return $this->getOptions($item['selected_wh'], $item['qtyOrdered']);
            }, $productsByWh
        );
        $optimizedOptions = $this->getOptimizedOptions($possibleOptionsToFulfill);
        $productsWithInventoryData = [];

        $noAllocationWarehouse = new Warehouse();
        $noAllocationWarehouse->setWarehouseType(new WarehouseType('virtual'));
        $noAllocationWarehouse->setCode('could_not_allocate');
        $warehouses[$noAllocationWarehouse->getCode()] = $noAllocationWarehouse;
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
                $productsWithInventoryData[$sku][] = [
                    'sku' => $sku,
                    'wh' => $noAllocationWarehouse->getCode(),
                    'qty' => $data['qtyOrdered'] - $data['qtyAvailable'],
                    'qtyOrdered' => $data['qtyOrdered']
                ];
            }
        }

        return $this->minQtyWHCalculator->calculate($productsWithInventoryData, $orderItemsByProducts, $warehouses, $orderItems);
    }

    private function getOptimizedOptions($allFoundOptions): array
    {
        $whIdsPerOption = [];


        // per product, get unique combinations of warehouses involved
        foreach ($allFoundOptions as $sku => $options) {
            foreach ($options as $optionId => $option) {
                $whIdsPerOption[$sku][$optionId] = implode(',',array_keys($option));
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
        $whIdsPerOption = array_merge(array_flip(array_keys($productOrder)), $whIdsPerOption);
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

        // sort the array with unique results by least total of warehouses
        // we could, if and when necessary, apply different sorting, for example, by distance.
        // it does however, need some modification as we do not have all data present for different sorting.
        uasort($unique, function($resultA, $resultB) {
           return count($resultA) <=> count($resultB);
        });

        // first item in the array is option with the least total of warehouses.
        $firstResult = array_shift($unique);

        // find the correct warehouse per product after the ideal result for warehouses have been found,
        // this necessary as we need the warehouses per product in order to generate the OrderWarehouseResult.
        return array_map(function($item) use ($firstResult) {
            if (count($item) === 1) {
                // we just need the first result
                return explode(',', array_shift($item));
            }
            // filter the options whether the warehouse from 'all options' are in the result of the optimized option
            // if not, we can't use that option.
            $filterOptions = array_filter($item, function($option) use ($firstResult, $item) {
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

    protected function getAllocatedQty($inventoryLevel, $orderItem, $orderItemQtyToAllocateLeft, $i)
    {
        if (($orderItem->getQuantity() < $inventoryLevel->getVirtualInventoryQty()
            || $orderItem->getQuantity() < $orderItemQtyToAllocateLeft)
        ) {
            return $orderItem->getQuantity();
        }

        if ($i === 0) {
            return $inventoryLevel->getVirtualInventoryQty();
        }

        if ($orderItemQtyToAllocateLeft > $inventoryLevel->getVirtualInventoryQty()) {
            return $inventoryLevel->getVirtualInventoryQty();
        }

        return $orderItemQtyToAllocateLeft;
    }

    protected function getInventoryLevelCandidates(InventoryItem $inventoryItem, array $warehouses)
    {
        // filter levels that either; have an warehouse in the linked warehouses, have virtual inventory > 0 or have an external warehouse (dropshipping)
        $filteredLevels = $inventoryItem
            ->getInventoryLevels()
            ->filter(function(InventoryLevel $inventoryLevel) use ($warehouses) {
                if (in_array($inventoryLevel->getWarehouse()->getId(), $warehouses)) {
                    return ($inventoryLevel->getVirtualInventoryQty() > 0 || $inventoryLevel->getWarehouse()->getWarehouseType()->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
                }

                return false;
            });

        $inventoryLevelIterator = $filteredLevels->getIterator();
        // sorting the inventorylevels that have the most stock on hand (virtual inventory) in a certain warehouse
        $inventoryLevelIterator->uasort(function(InventoryLevel $a, InventoryLevel $b) {
            return $b->getVirtualInventoryQty() <=> $a->getVirtualInventoryQty();
        });

        return new ArrayCollection(iterator_to_array($inventoryLevelIterator, false));
    }

    /**
     * @param OrderItem $orderItem
     * @param InventoryLevel $inventoryLevel
     * @param $type string
     * @return bool
     */
    protected function isWarehouseEligible(OrderItem $orderItem, InventoryLevel $inventoryLevel, $type = 'full')
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

    protected function isItemAvailable(OrderItem $orderItem, InventoryItem $inventoryItem, $type = null)
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
     * @param Product $product
     * @return Warehouse|null
     */
    protected function getPreferredExternalWarehouse(Product $product)
    {
        $preferredSupplier = null;
        $preferredPriority = 0;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if (null == $preferredSupplier && $productSupplierRelation->getCanDropship() === true) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
                continue;
            }
            if ($productSupplierRelation->getPriority() < $preferredPriority  &&
                $productSupplierRelation->getCanDropship() === true) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
            }
        }

        return $preferredSupplier;
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
