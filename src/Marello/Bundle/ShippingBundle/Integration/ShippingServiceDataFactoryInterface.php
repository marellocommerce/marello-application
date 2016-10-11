<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;

interface ShippingServiceDataFactoryInterface
{
    public function createData(ShippingServiceDataProviderInterface $shippingAwareInterface);
}
