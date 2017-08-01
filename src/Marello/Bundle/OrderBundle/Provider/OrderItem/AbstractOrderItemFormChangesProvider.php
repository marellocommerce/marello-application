<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;

abstract class AbstractOrderItemFormChangesProvider implements FormChangesProviderInterface
{
    const IDENTIFIER_PREFIX = 'product-id-';
    const ITEMS_FIELD = 'items';
    const CHANNEL_FIELD = 'salesChannel';
}
