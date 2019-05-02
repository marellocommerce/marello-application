<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Model;

use Doctrine\Common\Collections\ArrayCollection;

interface LineItemsAwareInterface
{
    /**
     * @return ArrayCollection
     */
    public function getItems();
}
