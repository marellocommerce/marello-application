<?php

namespace Marello\Bundle\ProductBundle\Model;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;

interface ProductAwareInterface
{
    public function getProduct();

    public function setProduct(ProductInterface $product);
}
