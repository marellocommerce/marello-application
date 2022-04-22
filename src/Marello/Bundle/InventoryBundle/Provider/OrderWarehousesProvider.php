<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategiesRegistry;
use Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\QuantityWFAStrategy;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /** @var bool $estimation */
    private $estimation = false;

    /**
     * @var WFAStrategiesRegistry
     */
    protected $strategiesRegistry;

    /**
     * @param WFAStrategiesRegistry $strategiesRegistry
     */
    public function __construct(WFAStrategiesRegistry $strategiesRegistry)
    {
        $this->strategiesRegistry = $strategiesRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setEstimation($estimation = false)
    {
        $this->estimation = $estimation;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order, Allocation $allocation = null): array
    {
        $results = [];
        $strategy = $this->strategiesRegistry->getStrategy(QuantityWFAStrategy::IDENTIFIER);
        $strategy->setEstimation($this->estimation);
        $results = $strategy->getWarehouseResults($order, $allocation, $results);

        if (count($results) > 0) {
            return $results;
        }

        return [];
    }
}
