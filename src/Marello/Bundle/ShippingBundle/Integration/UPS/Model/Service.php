<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class Service extends XmlSerializedModel
{
    const NODE_NAME = 'Service';

    public $code;

    public $description;
}
