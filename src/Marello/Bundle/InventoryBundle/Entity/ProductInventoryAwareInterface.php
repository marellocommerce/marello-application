<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;

interface ProductInventoryAwareInterface
{
    public function getProduct();

    public function setProduct(ProductInterface $product);
}
