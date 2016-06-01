<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;


class Packaging implements XMLSerializable
{
    const NODE_NAME = 'Packaging';

    use XMLSerializableTrait;

    public $code;
}
