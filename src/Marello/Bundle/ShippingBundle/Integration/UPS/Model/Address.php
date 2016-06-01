<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

use Marello\Bundle\AddressBundle\Entity\Address as MarelloAddress;

class Address implements XMLSerializable
{
    const NODE_NAME = 'Address';

    use XMLSerializableTrait;

    public $addressLine1;

    public $addressLine2;

    public $addressLine3;

    public $city;

    public $stateProvinceCode;

    public $postalCode;

    public $countryCode;

    /**
     * @param MarelloAddress $marelloAddress
     *
     * @return Address
     */
    public static function fromAddress(MarelloAddress $marelloAddress)
    {
        $address = new self();

        $address->addressLine1      = $marelloAddress->getStreet();
        $address->addressLine2      = $marelloAddress->getStreet2();
        $address->city              = $marelloAddress->getCity();
        $address->stateProvinceCode = $marelloAddress->getRegionCode();
        $address->postalCode        = $marelloAddress->getPostalCode();
        $address->countryCode       = $marelloAddress->getCountryIso2();

        return $address;
    }
}
