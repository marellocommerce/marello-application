<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

use DOMDocument;

class AccessRequest extends XmlSerializedModel
{
    const NODE_NAME = 'AccessRequest';

    /** @var string */
    protected $userId;

    /** @var string */
    protected $password;

    /** @var string */
    protected $accessLicenseNumber;

    /**
     * AccessRequest constructor.
     *
     * @param string $userId
     * @param string $password
     * @param string $accessLicenseNumber
     */
    public function __construct($userId, $password, $accessLicenseNumber)
    {
        $this->userId              = $userId;
        $this->password            = $password;
        $this->accessLicenseNumber = $accessLicenseNumber;
    }
    
    public function toXmlNode(DOMDocument $xml)
    {
        $node = parent::toXmlNode($xml);

        $node->setAttribute('xml:lang', 'en-US');

        return $node;
    }
}
