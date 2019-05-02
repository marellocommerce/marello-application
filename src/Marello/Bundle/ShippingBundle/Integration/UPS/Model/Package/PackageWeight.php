<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\XmlSerializedModel;

class PackageWeight extends XmlSerializedModel
{
    const NODE_NAME = 'PackageWeight';

    /** @var UnitOfMeasurement */
    public $unitOfMeasurement;

    public $weight;
}
