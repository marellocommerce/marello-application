<?php

namespace Marello\Bundle\ProductBundle\Event;

class ProductDuplicateAfterEvent extends AbstractProductDuplicateEvent
{
    const NAME = 'marello_product.product.duplicate.after';
}
