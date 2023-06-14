<?php

namespace Marello\Bundle\InventoryBundle\Provider;

interface AllocationContextInterface
{
    const ALLOCATION_CONTEXT_ENUM_CODE           = 'marello_allocation_allocationcontext';
    const ALLOCATION_CONTEXT_ORDER               = 'order';
    const ALLOCATION_CONTEXT_REALLOCATION        = 'reallocation';
    const ALLOCATION_CONTEXT_CONSOLIDATION       = 'consolidation';
    const ALLOCATION_CONTEXT_RESHIPMENT          = 'reshipment';
}
