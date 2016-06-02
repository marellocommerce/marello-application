<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;


class Package implements XMLSerializable
{
    use XMLSerializableTrait;

    const NODE_NAME = 'Package';

    public $packaging;
}
