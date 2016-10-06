<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;

interface ShippingServiceDataFactoryInterface
{
    public function createData(ShippingAwareInterface $shippingAwareInterface);
}
