<?php

namespace Marello\Bundle\OroCommerceBundle\Event;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\EventDispatcher\Event;

class RemoteProductCreatedEvent extends Event
{
    const NAME = 'marello_orocommerce.remote_product_created';

    /**
     * @var Product
     */
    private $product;

    /**
     * @var SalesChannel
     */
    private $salesChannel;

    /**
     * @param Product $product
     * @param SalesChannel $salesChannel
     */
    public function __construct(Product $product, SalesChannel $salesChannel)
    {
        $this->product = $product;
        $this->salesChannel = $salesChannel;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
    
    /**
     * @return SalesChannel
     */
    public function getSalesChannel()
    {
        return $this->salesChannel;
    }
}