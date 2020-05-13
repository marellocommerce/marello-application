<?php

namespace Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Factory;

use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\PaymentLineItemBuilderInterface;

interface PaymentLineItemBuilderFactoryInterface
{
    /**
     * @param int                    $quantity
     * @param ProductAwareInterface $productHolder
     *
     * @return PaymentLineItemBuilderInterface
     */
    public function createBuilder(
        $quantity,
        ProductAwareInterface $productHolder
    );
}
