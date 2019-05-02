<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class ShipTo extends XmlSerializedModel
{
    const NODE_NAME = 'ShipTo';

    /** @var string */
    public $companyName;

    /** @var string */
    public $attentionName;

    /** @var string */
    public $taxIdentificationNumber;

    /** @var string */
    public $phoneNumber;

    /** @var string */
    public $faxNumber;

    /** @var string */
    public $eMailAddress;

    /** @var Address */
    public $address;

    protected function filterProperties($property, $value)
    {
        return $this->$property;
    }
}
