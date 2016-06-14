<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\XmlSerializedModel;

class UnitOfMeasurement extends XmlSerializedModel
{
    const NODE_NAME = 'UnitOfMeasurement';

    public $code;
}
