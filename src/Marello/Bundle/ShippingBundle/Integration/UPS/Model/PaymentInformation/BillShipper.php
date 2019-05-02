<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model\PaymentInformation;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\XmlSerializedModel;

class BillShipper extends XmlSerializedModel
{
    const NODE_NAME = 'BillShipper';

    /** @var string */
    public $accountNumber;
}
