<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Order;
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

        $linkedWarehouses = $this->warehouseChannelGroupLinkRepository
            ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup())
            ->getWarehouseGroup()
            ->getWarehouses()
            ->toArray();
        if (empty($linkedWarehouses)) {
            return null;
        }
        $linkedWarehousesIds = array_map(function( Warehouse $warehouse) {
            return $warehouse->getId();
        }, $linkedWarehouses);

        foreach ($orderItems as $orderItem) {
            $orderItemsByProducts[$orderItem->getProduct()->getSku()] = $orderItem;
            $inventoryItems = $orderItem->getInventoryItems();
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    $warehouse = $inventoryLevel->getWarehouse();
                    $warehouseId = $warehouse->getId();
                    if ($inventoryLevel->getInventoryQty() >= $orderItem->getQuantity() &&
                        in_array($warehouseId, $linkedWarehousesIds)) {
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
}
