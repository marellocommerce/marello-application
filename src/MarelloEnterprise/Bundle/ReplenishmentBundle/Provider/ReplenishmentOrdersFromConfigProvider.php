<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Provider;

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
                    ->setPercentage($config->getPercentage())
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
            if ($calculateQuantities) {
                $orderItem
                    ->setInventoryQty($result['quantity'])
                    ->setTotalInventoryQty($result['total_quantity']);
            }
            $order->addReplOrderItem($orderItem);
        }

        return $orders;
    }
}
