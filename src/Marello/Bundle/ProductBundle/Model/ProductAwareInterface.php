<?php

namespace Marello\Bundle\ProductBundle\Model;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;

interface ProductAwareInterface
{
    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @param ProductInterface $product
     * @return $this
     */
    public function setProduct(ProductInterface $product);
}
