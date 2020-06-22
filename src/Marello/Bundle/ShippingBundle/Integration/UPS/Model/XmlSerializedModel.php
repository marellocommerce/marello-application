<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\Model;

use DOMDocument;
use DOMElement;

abstract class XmlSerializedModel implements XMLSerializable
{
    public function toXmlNode(DOMDocument $xml)
    {
        $node = $xml->createElement($this->getNodeName());

        $properties = get_object_vars($this);

        foreach ($properties as $property => $value) {
            if (!$this->filterProperties($property, $value)) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    $this->addNode($xml, $node, $property, $item);
                }
            } else {
                $this->addNode($xml, $node, $property, $value);
            }
        }

        return $node;
    }

    protected function filterProperties($property, $value)
    {
        return true;
    }

    /**
     * @param DOMDocument $xml
     * @param DOMElement  $parent
     * @param string      $property
     * @param mixed       $value
     */
    private function addNode(DOMDocument $xml, DOMElement $parent, $property, $value)
    {
        if ($value instanceof XMLSerializable) {
            $parent->appendChild($value->toXmlNode($xml));
        } else {
            $parent->appendChild($xml->createElement(ucfirst($property), $value));
        }
    }

    /**
     * @return string
     */
    protected function getNodeName()
    {
        return static::NODE_NAME;
    }
}
