<?php

namespace Marello\Bundle\InventoryBundle\Model\Allocation\Notifier;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierInterface;

class WarehouseManualNotifier implements WarehouseNotifierInterface
{
    const IDENTIFIER = 'manual_notifier';
    const LABEL = 'marello.inventory.warehouse_notifier.manual';

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

    public function notifyWarehouse(Allocation $allocation)
    {
        // manual notification, do nothing
        return;
    }
}
