<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

interface XMLSerializable
{
    public function toXmlNode(\DOMDocument $xml);
}
