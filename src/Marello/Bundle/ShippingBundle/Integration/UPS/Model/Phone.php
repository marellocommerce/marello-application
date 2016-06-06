<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;


class Phone implements XMLSerializable
{
    const NODE_NAME = 'Phone';

    use XMLSerializableTrait;

    public $number;
}
