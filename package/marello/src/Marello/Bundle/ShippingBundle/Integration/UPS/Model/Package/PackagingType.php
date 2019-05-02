<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\XmlSerializedModel;

class PackagingType extends XmlSerializedModel
{
    const NODE_NAME = 'PackagingType';

    /** @var string */
    public $code;

    /** @var string */
    public $description;

    /**
     * PackagingType constructor.
     *
     * @param string $code
     * @param string $description
     */
    public function __construct($code, $description)
    {
        $this->code        = $code;
        $this->description = $description;
    }
}
