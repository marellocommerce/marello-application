<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class Shipper extends XmlSerializedModel
{
    const NODE_NAME = 'Shipper';

    /**
     * Shipper's company name. For forward Shipment 35 characters are accepted, but only 30 characters will be printed
     * on the label.
     *
     * @var string
     */
    public $name;

    /**
     * Shipper's Attention Name. For forward Shipment 35 characters are accepted, but only 30 characters will be
     * printed on the label.
     *
     * @var string
     */
    public $attentionName;

    /**
     * Shipper's CompanyDisplayableName
     *
     * @var string
     */
    public $companyDisplayableName;

    /**
     * Shipper’s Phone Number
     *
     * @var string
     */
    public $phoneNumber;

    /**
     * Shipper’s Fax Number.
     *
     * @var string
     */
    public $faxNumber;

    /**
     * Shipper’s email address.
     *
     * @var string
     */
    public $eMailAddress;

    /**
     * Shipper’s Tax Identification Number
     *
     * @var string
     */
    public $taxIdentificationNumber;

    /**
     * Shipper's six digit account number.
     *
     * @var string
     */
    public $shipperNumber;

    /**
     * @var Address
     */
    public $address;

    protected function filterProperties($property, $value)
    {
        return $this->$property;
    }
}
