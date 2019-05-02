<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model\PaymentInformation;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\XmlSerializedModel;

class Prepaid extends XmlSerializedModel
{
    const NODE_NAME = 'Prepaid';

    /** @var BillShipper */
    public $billShipper;
}
