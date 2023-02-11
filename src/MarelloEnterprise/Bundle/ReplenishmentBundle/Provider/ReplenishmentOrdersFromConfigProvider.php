<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Provider;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ReplenishmentStrategiesRegistry;

class ReplenishmentOrdersFromConfigProvider
{
    /**
     * @var ReplenishmentStrategiesRegistry
     */
    protected $replenishmentStrategiesRegistry;

    /**
     * @param ReplenishmentStrategiesRegistry $replenishmentStrategiesRegistry
     */
    public function __construct(ReplenishmentStrategiesRegistry $replenishmentStrategiesRegistry)
    {
        $this->replenishmentStrategiesRegistry = $replenishmentStrategiesRegistry;
    }

    /**
     * @param ReplenishmentOrderConfig $config
     * @param bool $calculateQuantities
     * @return ReplenishmentOrder[]
     */
    public function getReplenishmentOrders(ReplenishmentOrderConfig $config, $calculateQuantities = false)
    {
        $strategy = $this->replenishmentStrategiesRegistry->getStrategy($config->getStrategy());
        $replenishmentResults = $strategy->getResults($config);

        if (empty($replenishmentResults)) {
            return [];
        }

        $orders = [];
        foreach ($replenishmentResults as $result) {
            /** @var Warehouse $origin */
            $origin = $result['origin'];
            /** @var Warehouse $destination */
            $destination = $result['destination'];
            /** @var Product $product */
            $product = $result['product'];

            if (!isset($orders[sprintf('%s-%s', $origin->getId(), $destination->getId())])) {
                $order = new ReplenishmentOrder();
                $order
                    ->setOrganization($config->getOrganization())
                    ->setOrigin($origin)
                    ->setDestination($destination)
                    ->setExecutionDateTime($config->getExecutionDateTime())
                    ->setReplOrderConfig($config)
                    ->setDescription($config->getDescription());
                $orders[sprintf('%s-%s', $origin->getId(), $destination->getId())] = $order;
            }
            /** @var ReplenishmentOrder $order */
            $order = $orders[sprintf('%s-%s', $origin->getId(), $destination->getId())];
            $orderItem = new ReplenishmentOrderItem();
            $orderItem
                ->setProduct($product)
                ->setOrder($order);
            if (!empty($result['allQuantity'])) {
                $orderItem->setAllQuantity((bool) $result['allQuantity']);
            }
            if ($calculateQuantities) {
                $orderItem
                    ->setInventoryQty($result['quantity'])
                    ->setTotalInventoryQty($result['total_quantity']);
                /** @var InventoryItem $inventoryItem */
                $inventoryItem = $product->getInventoryItems()->first();
                if ($inventoryItem && $inventoryItem->isEnableBatchInventory()) {
                    if ($inventoryLevel = $inventoryItem->getInventoryLevel($origin)) {
                        /** @var InventoryBatch[] $inventoryBatches */
                        $inventoryBatches = $inventoryLevel->getInventoryBatches()->toArray();
                        if (count($inventoryBatches) > 0) {
                            usort($inventoryBatches, function (InventoryBatch $a, InventoryBatch $b) {
                                if ($a->getDeliveryDate() < $b->getDeliveryDate()) {
                                    return -1;
                                } elseif ($a->getDeliveryDate() > $b->getDeliveryDate()) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            });
                            $data = [];
                            $quantity = $orderItem->getInventoryQty();
                            /** @var InventoryBatch[] $inventoryBatches */
                            foreach ($inventoryBatches as $inventoryBatch) {
                                if ($inventoryBatch->getQuantity() >= $quantity) {
                                    $data[$inventoryBatch->getBatchNumber()] = $quantity;
                                    break;
                                } elseif ($batchQty = $inventoryBatch->getQuantity() > 0) {
                                    $data[$inventoryBatch->getBatchNumber()] = $batchQty;
                                    $quantity = $quantity - $batchQty;
                                }
                            }
                            $orderItem->setInventoryBatches($data);
                        }
                    }
                }
            }
            $order->addReplOrderItem($orderItem);
        }

        return $orders;
    }
}
