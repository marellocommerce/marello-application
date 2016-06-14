<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package\PackageWeight;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package\PackagingType;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package\ReferenceNumber;

class Package extends XmlSerializedModel
{
    const NODE_NAME = 'Package';

    /** @var PackagingType */
    public $packagingType;

    /** @var string */
    public $description;

    /** @var PackageWeight */
    public $packageWeight;

    /** @var ReferenceNumber */
//    public $referenceNumber;

    /** @var string */
//    public $largePackageIndicator;

    /** @var string */
//    public $additionalHandling = '0';
}
