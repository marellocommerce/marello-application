<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

use Marello\Bundle\ShippingBundle\Integration\UPS\Model\PaymentInformation\Prepaid;

class PaymentInformation extends XmlSerializedModel
{
    const NODE_NAME = 'PaymentInformation';

    /** @var Prepaid */
    public $prepaid;
}
