<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\Factory;

use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\DoctrineShippingLineItemCollection;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Factory\ShippingLineItemCollectionFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

class DoctrineShippingLineItemCollectionFactory implements ShippingLineItemCollectionFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createShippingLineItemCollection(array $shippingLineItems): ShippingLineItemCollectionInterface
    {
        foreach ($shippingLineItems as $shippingLineItem) {
            if (!$shippingLineItem instanceof ShippingLineItemInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Expected: %s', ShippingLineItemInterface::class)
                );
            }
        }

        return new DoctrineShippingLineItemCollection($shippingLineItems);
    }
}
