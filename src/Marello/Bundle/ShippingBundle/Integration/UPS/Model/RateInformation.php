<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

class RateInformation extends XmlSerializedModel
{
    const NODE_NAME = 'RateInformation';

    public $negotiatedRatesIndicator;
}
