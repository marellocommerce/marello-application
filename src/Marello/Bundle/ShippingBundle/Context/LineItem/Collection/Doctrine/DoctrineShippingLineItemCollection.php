<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

class DoctrineShippingLineItemCollection extends ArrayCollection implements ShippingLineItemCollectionInterface
{
    /**
     * @param array|ShippingLineItemInterface[] $elements
     */
    public function __construct(array $elements)
    {
        parent::__construct($elements);
    }
}
