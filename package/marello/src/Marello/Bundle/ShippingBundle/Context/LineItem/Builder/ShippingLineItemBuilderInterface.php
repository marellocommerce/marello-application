<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Builder;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

interface ShippingLineItemBuilderInterface
{
    /**
     * @return ShippingLineItemInterface
     */
    public function getResult();

    /**
     * @param Product $product
     *
     * @return self
     */
    public function setProduct(Product $product);

    /**
     * @param string $sku
     *
     * @return self
     */
    public function setProductSku($sku);

    /**
     * @param float $weight
     *
     * @return self
     */
    public function setWeight($weight);

    /**
     * @param Price $price
     *
     * @return self
     */
    public function setPrice(Price $price);
}
