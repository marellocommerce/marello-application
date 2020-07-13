<?php

namespace Marello\Bundle\OrderBundle\Model;

interface OrderStatusesInterface
{
    const ORDER_STATUS_ENUM_CLASS = 'marello_order_status';

    const OS_PENDING = 'Pending';
    const OS_CANCELLED = 'Cancelled';
    const OS_INVOICED = 'Invoiced';
    const OS_PAID = 'Paid';
    const OS_PARTIALLY_PAID = 'Partially Paid';
    const OS_PICK_AND_PACK = 'Pick and Pack';
    const OS_SHIPPED = 'Shipped';
    const OS_PARTIALLY_SHIPPED = 'Partially Shipped';
    const OS_CLOSED = 'Closed';
    const OS_ON_HOLD = 'Hold';
    const OS_PROCESSING = 'Processing';
}
