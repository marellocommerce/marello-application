<?php

namespace Marello\Bundle\InventoryBundle\Async;

final class Topics
{
    const ALL_INVENTORY = 'all';

    const RESOLVE_REBALANCE_INVENTORY = 'marello_inventory.inventory_rebalance';
    const RESOLVE_REBALANCE_ALL_INVENTORY = 'marello_inventory.inventory_rebalance_all';

    const INVENTORY_LOG_RECORD_CREATE = 'marello_inventory.inventorylevellogrecord.create';
}
