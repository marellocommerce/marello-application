<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;

interface WFAStrategyInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return boolean
     */
    public function isEnabled(): bool;

    /**
     * @param Order $order
     * @param Allocation|null $allocation
     * @param OrderWarehouseResult[] $initialResults
     * @return OrderWarehouseResult[]
     */
    public function getWarehouseResults(Order $order, Allocation $allocation = null, array $initialResults = []): array;
}
