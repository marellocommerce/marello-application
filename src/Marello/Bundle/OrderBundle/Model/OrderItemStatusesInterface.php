<?php

namespace Marello\Bundle\OrderBundle\Model;

interface OrderItemStatusesInterface
{
    const ITEM_STATUS_ENUM_CLASS = 'marello_item_status';

    const OIS_PENDING = 'pending';
    const OIS_PROCESSING = 'processing';
    const OIS_SHIPPED = 'shipped';
    const OIS_COMPLETE = 'complete';

}
