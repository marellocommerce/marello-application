<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;

class MinimumQuantityWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_quantity';
    const LABEL = 'marelloenterprise.inventory.strategies.min_quantity';

    /**
     * @var MinQtyWHCalculatorInterface
     */
    private $minQtyWHCalculator;

    /**
     * @var WarehouseChannelGroupLinkRepository
     */
    private $warehouseChannelGroupLinkRepository;

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
     * {@inheritdoc}
     */
    public function getWarehouseResults(Order $order, array $initialResults = [])
    {
        $productsByWh = [];
        $warehouses = [];
        $orderItems = $order->getItems();
        $orderItemsByProducts = [];

        $externalWarehouses = $this->getExternalWarehouses($order);
        $linkedWarehouses = $this->getLinkedWarehouses($order);

        if (empty($linkedWarehouses) && empty($externalWarehouses)) {
            return [];
        }
        $warehousesIds = array_map(function (Warehouse $warehouse) {
            return $warehouse->getId();
        }, array_merge($linkedWarehouses, $externalWarehouses));

        foreach ($orderItems as $orderItem) {
            $orderItemsByProducts[sprintf('%s_|_%s', $orderItem->getProductSku(), $orderItem->getId())] = $orderItem;
            $inventoryItems = $orderItem->getInventoryItems();
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    $warehouse = $inventoryLevel->getWarehouse();
                    $warehouseType = $warehouse->getWarehouseType()->getName();
                    $warehouseId = $warehouse->getId();
                    if (($inventoryLevel->getInventoryQty() >= $orderItem->getQuantity() ||
                            $warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) &&
                        in_array($warehouseId, $warehousesIds)) {
                        $warehouses[$warehouseId] = $warehouse;
                        $productsByWh[$warehouseId] [] = $inventoryItem->getProduct()->getSku();
                    }
                }
            }
        }

        uasort($productsByWh, function ($a, $b) {
            return count($b) > count($a) ? 1 : -1 ;
        });

        return $this->minQtyWHCalculator->calculate($productsByWh, $orderItemsByProducts, $warehouses, $orderItems);
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getExternalWarehouses(Order $order)
    {
        $warehouses = [];
        foreach ($order->getItems() as $orderItem) {
            $product = $orderItem->getProduct();
            $inventoryItems = $product->getInventoryItems();
            $preferedSupplier = $this->getPreferredSupplierWhichCanDropship($product);
            $supplierWarehouseCode = sprintf(
                '%s_external_warehouse',
                str_replace(' ', '_', strtolower($preferedSupplier->getName()))
            );
            foreach ($inventoryItems as $inventoryItem) {
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    $warehouse = $inventoryLevel->getWarehouse();
                    if ($warehouse->getWarehouseType()->getName() ===
                        WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL &&
                        $supplierWarehouseCode === $warehouse->getCode()) {
                        $warehouses[$warehouse->getId()] = $warehouse;
                    }
                }
            }
        }

        return $warehouses;
    }

    /**
     * @param Product $product
     * @return Supplier|null
     */
    protected function getPreferredSupplierWhichCanDropship(Product $product)
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
     * @return array
     */
    private function getLinkedWarehouses(Order $order)
    {
        $warehouseGroupLink = $this->warehouseChannelGroupLinkRepository
            ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup());

        if (!$warehouseGroupLink) {
            return [];
        }

        $linkedWarehouses = $warehouseGroupLink
            ->getWarehouseGroup()
            ->getWarehouses()
            ->toArray();

        return $linkedWarehouses;
    }
}
