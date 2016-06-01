<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;


class Package implements XMLSerializable
{
    public $packaging;

    public function toXmlNode(\DOMDocument $xml, \DOMElement $parent)
    {
        // TODO: Implement toXmlNode() method.
    }
}
