<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Collection;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

interface ShippingLineItemCollectionInterface extends Collection
{
    /**
     * @return ShippingLineItemInterface
     */
    public function current();

    /**
     * @param int|string $key
     *
     * @return ShippingLineItemInterface
     */
    public function get($key);

    /**
     * @return ShippingLineItemInterface
     */
    public function first();

    /**
     * @return ShippingLineItemInterface
     */
    public function last();

    /**
     * @return ShippingLineItemInterface
     */
    public function next();

    /**
     * @param int|string $key
     *
     * @return ShippingLineItemInterface
     */
    public function remove($key);
}
