<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\XmlSerializedModel;

class ReferenceNumber extends XmlSerializedModel
{
    const NODE_NAME = 'ReferenceNumber';

    /** @var string */
    public $code;

    /** @var string */
    public $value;

    /**
     * ReferenceNumber constructor.
     *
     * @param string $code
     * @param string $value
     */
    public function __construct($code, $value)
    {
        $this->code  = $code;
        $this->value = $value;
    }
}
