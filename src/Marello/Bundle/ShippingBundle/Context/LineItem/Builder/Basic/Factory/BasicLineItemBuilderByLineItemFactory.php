<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\Factory;

use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\LineItemBuilderByLineItemFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\ShippingLineItemBuilderFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

class BasicLineItemBuilderByLineItemFactory implements LineItemBuilderByLineItemFactoryInterface
{
    /**
     * @var ShippingLineItemBuilderFactoryInterface
     */
    private $builderFactory;

    /**
     * @param ShippingLineItemBuilderFactoryInterface $builderFactory
     */
    public function __construct(ShippingLineItemBuilderFactoryInterface $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createBuilder(ShippingLineItemInterface $lineItem)
    {
        $builder = $this->builderFactory->createBuilder(
            $lineItem->getQuantity(),
            $lineItem->getProductHolder()
        );

        if (null !== $lineItem->getProduct()) {
            $builder->setProduct($lineItem->getProduct());
        }

        if (null !== $lineItem->getProductSku()) {
            $builder->setProductSku($lineItem->getProductSku());
        }

        if (null !== $lineItem->getPrice()) {
            $builder->setPrice($lineItem->getPrice());
        }

        if (null !== $lineItem->getWeight()) {
            $builder->setWeight($lineItem->getWeight());
        }

        return $builder;
    }
}
