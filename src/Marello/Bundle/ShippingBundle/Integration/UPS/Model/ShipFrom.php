<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class ShipFrom extends XmlSerializedModel
{
    const NODE_NAME = 'ShipFrom';

    /** @var string */
    public $companyName;

    /** @var string */
    public $attentionName;

    /** @var string */
    public $phoneNumber;

    /** @var string */
    public $taxIdentificationNumber;

    /** @var Address */
    public $address;
}
