<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;


trait XMLSerializableTrait
{
    public function toXmlNode(\DOMDocument $xml, \DOMElement $parent)
    {
        $properties = get_object_vars($this);

        $parent->appendChild($node = $xml->createElement(self::NODE_NAME));

        foreach($properties as $property => $value) {
            if (empty($value)) {
                continue;
            }

            if ($value instanceof XMLSerializable) {
                $value->toXmlNode($xml, $node);
            }

            $node->appendChild($xml->createElement(ucfirst($property), $value));
        }
    }
}
