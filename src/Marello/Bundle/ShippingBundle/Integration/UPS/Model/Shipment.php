<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;


class Shipment implements XMLSerializable
{
    const NODE_NAME = 'Shipment';

    use XMLSerializableTrait;

    /** @var Shipper */
    public $shipper;

    /** @var ShipTo */
    public $shipTo;

    /** @var Service */
    public $service;

    /** @var Package */
    public $package;
}
