<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class Service extends XmlSerializedModel
{
    const NODE_NAME = 'Service';

    public $code;

    public $description;

    /**
     * Service constructor.
     *
     * @param $code
     * @param $description
     */
    public function __construct($code, $description = null)
    {
        $this->code        = $code;
        $this->description = $description;
    }
}
