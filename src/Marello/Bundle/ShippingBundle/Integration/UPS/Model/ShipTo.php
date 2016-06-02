<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class ShipTo implements XMLSerializable
{
    const NODE_NAME = 'ShipTo';

    use XMLSerializableTrait;

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

    /** @var string */
    public $locationID;
}
