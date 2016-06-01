<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class Service implements XMLSerializable
{
    const NODE_NAME = 'Service';

    use XMLSerializableTrait;

    public $code;
}
