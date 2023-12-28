<?php

namespace Marello\Bundle\InventoryBundle\Provider;

interface AllocationContextInterface
{
    const ALLOCATION_CONTEXT_ENUM_CODE           = 'marello_allocation_allocationcontext';
    const ALLOCATION_CONTEXT_ORDER               = 'order';
    const ALLOCATION_CONTEXT_REALLOCATION        = 'reallocation';
    const ALLOCATION_CONTEXT_CONSOLIDATION       = 'consolidation';
    const ALLOCATION_CONTEXT_RESHIPMENT          = 'reshipment';
    const ALLOCATION_CONTEXT_CASH_CARRY          = 'order_cash_and_carry';
}
