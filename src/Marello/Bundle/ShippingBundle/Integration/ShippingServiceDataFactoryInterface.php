<?php

namespace Marello\Bundle\ShippingBundle\Integration;

interface ShippingServiceDataFactoryInterface
{
    public function createData(ShippingServiceDataProviderInterface $shippingAwareInterface);
}
