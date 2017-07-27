<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

interface OrderItemDataProviderInterface
{
    const IDENTIFIER_PREFIX = 'product-id-';
 
    /**
     * @param int $channelId
     * @param array $products
     * @return array
     */
    public function getData($channelId, array $products);
}
