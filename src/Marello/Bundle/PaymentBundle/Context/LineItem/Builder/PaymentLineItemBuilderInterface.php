<?php

namespace Marello\Bundle\PaymentBundle\Context\LineItem\Builder;

use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\CurrencyBundle\Entity\Price;

interface PaymentLineItemBuilderInterface
{
    /**
     * @return PaymentLineItemInterface
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
