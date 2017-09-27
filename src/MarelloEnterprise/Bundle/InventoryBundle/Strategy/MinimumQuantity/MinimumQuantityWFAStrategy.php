<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
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
     * @param MinQtyWHCalculatorInterface $minQtyWHCalculator
     */
    public function __construct(MinQtyWHCalculatorInterface $minQtyWHCalculator)
    {
        $this->minQtyWHCalculator = $minQtyWHCalculator;
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
        foreach ($orderItems as $orderItem) {
            $orderItemsByProducts[$orderItem->getProduct()->getSku()] = $orderItem;
            $inventoryItems = $orderItem->getInventoryItems();
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    if ($inventoryLevel->getInventoryQty() >= $orderItem->getQuantity()) {
                        $warehouse = $inventoryLevel->getWarehouse();
                        $warehouses[$warehouse->getId()] = $warehouse;
                        $productsByWh[$warehouse->getId()] [] = $inventoryItem->getProduct()->getSku();
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
