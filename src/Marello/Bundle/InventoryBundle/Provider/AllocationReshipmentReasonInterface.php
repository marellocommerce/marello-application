<?php

namespace Marello\Bundle\InventoryBundle\Provider;

interface AllocationReshipmentReasonInterface
{
    const ALLOCATION_RESHIPMENT_REASON_ENUM_CODE  = 'marello_allocation_reshipmentreason';
    const ALLOCATION_RESHIPMENT_REASON_LOST       = 'lost';
    const ALLOCATION_RESHIPMENT_REASON_WRONG_ITEM = 'wrong_item';
    const ALLOCATION_RESHIPMENT_REASON_DAMAGED    = 'damaged';
    const ALLOCATION_RESHIPMENT_REASON_OTHER      = 'other';
}
