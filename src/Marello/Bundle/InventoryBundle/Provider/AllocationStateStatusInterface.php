<?php

namespace Marello\Bundle\InventoryBundle\Provider;

interface AllocationStateStatusInterface
{
    const ALLOCATION_STATE_ENUM_CODE = 'marello_allocation_state';
    const ALLOCATION_STATE_AVAILABLE = 'available';
    const ALLOCATION_STATE_WFS       = 'waiting';
    const ALLOCATION_STATE_ALERT     = 'alert';

    const ALLOCATION_STATUS_ENUM_CODE   = 'marello_allocation_status';
    const ALLOCATION_STATUS_ON_HAND     = 'on_hand';
    const ALLOCATION_STATUS_DROPSHIP    = 'dropshipping';
    const ALLOCATION_STATUS_BACK_ORDER  = 'backorder';
    const ALLOCATION_STATUS_PRE_ORDER   = 'preorder';
    const ALLOCATION_STATUS_CNA         = 'could_not_allocate';
}
