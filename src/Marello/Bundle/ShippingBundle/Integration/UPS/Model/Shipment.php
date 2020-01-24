<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class Shipment extends XmlSerializedModel
{
    const NODE_NAME = 'Shipment';

    /** @var RateInformation */
    public $rateInformation;

    /** @var string */
    public $description;

    /** @var Shipper */
    public $shipper;

    /** @var ShipTo */
    public $shipTo;

    /** @var ShipFrom */
    public $shipFrom;

    /** @var PaymentInformation */
    public $paymentInformation;

    /** @var Service */
    public $service;

    /** @var Package */
    public $package;

    protected function filterProperties($property, $value)
    {
        return ($property !== 'shipFrom' || $this->shipFrom);
    }
}
