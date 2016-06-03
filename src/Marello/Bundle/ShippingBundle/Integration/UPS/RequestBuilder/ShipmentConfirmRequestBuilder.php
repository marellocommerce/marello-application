<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;
use DOMElement;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;

class ShipmentConfirmRequestBuilder extends RequestBuilder
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function build(array $data)
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;

        $xml->appendChild($root = $xml->createElement('ShipmentConfirmRequest'));

        $this->buildRequest($xml, $root);
        $this->buildShipment($data, $xml, $root);

        return $xml->saveXML();
    }

    protected function buildRequest(DOMDocument $xml, DOMElement $parent)
    {
        $parent->appendChild($request = $xml->createElement('Request'));

        $request->appendChild($transaction = $xml->importNode($this->createTransactionNode(), true));

        $request->appendChild($xml->createElement('RequestAction', 'ShipConfirm'));
        $request->appendChild($xml->createElement('RequestOption', 'validate'));
    }

    protected function buildShipment(array $data, DOMDocument $xml, DOMElement $parent)
    {
        /** @var Shipment $shipment */
        $shipment = $data['shipment'];

        $shipment->toXmlNode($xml, $parent);
    }
}
