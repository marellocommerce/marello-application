<?php

namespace Marello\Bundle\InventoryBundle\Provider;

/**
 * Class of constants for different warehouse types
 * Interface WarehouseTypeProviderInterface
 * @package Marello\Bundle\InventoryBundle\Provider
 */
interface WarehouseTypeProviderInterface
{
    const WAREHOUSE_TYPE_FIXED      = 'fixed';
    const WAREHOUSE_TYPE_GLOBAL     = 'global';
    const WAREHOUSE_TYPE_VIRTUAL    = 'virtual';
}
