<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Collection\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Collection;

class DoctrineShippingMethodValidatorResultErrorCollection extends ArrayCollection implements
    Collection\ShippingMethodValidatorResultErrorCollectionInterface
{
    /**
     * {@inheritDoc}
     */
    public function createCommonBuilder()
    {
        return
            new Collection\Builder\Common\Doctrine\DoctrineCommonShippingMethodValidatorResultErrorCollectionBuilder();
    }
}
