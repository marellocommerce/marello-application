<?php

namespace Marello\Bundle\ShippingBundle\Context;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\ProductBundle\Model\ProductHolderInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;

interface ShippingLineItemInterface extends ProductHolderInterface
{
    /**
     * @return Price|null
     */
    public function getPrice();

    /**
     * @return Product|null
     */
    public function getProduct();

    /**
     * @return ProductAwareInterface|null
     */
    public function getProductHolder();

    /**
     * @return string
     */
    public function getProductSku();

    /**
     * @return mixed
     */
    public function getEntityIdentifier();

    /**
     * @return float
     */
    public function getQuantity();

    /**
     * @return float
     */
    public function getWeight();
}
