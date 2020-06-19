<?php

namespace Marello\Bundle\PricingBundle\Entity;

use Marello\Bundle\ProductBundle\Entity\Product;

interface PriceListInterface
{
    /**
     * @return BasePrice
     */
    public function getDefaultPrice();

    /**
     * @return BasePrice
     */
    public function getSpecialPrice();

    /**
     * @return Product
     */
    public function getProduct();

    /**
     * @return string
     */
    public function getCurrency();
}
