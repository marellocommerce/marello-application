<?php

namespace Marello\Bundle\ShippingBundle\Context;

interface ShippingContextFactoryInterface
{
    // estimation method (public function setEstimation($estimation = false)
    // will be included in 3.0, not in 2.2 because of BC breaks

    /**
     * @param object $entity
     *
     * @return ShippingContextInterface[]
     */
    public function create($entity);
}
