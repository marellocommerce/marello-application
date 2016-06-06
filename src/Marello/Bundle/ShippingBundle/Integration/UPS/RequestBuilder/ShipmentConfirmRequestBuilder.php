<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;
use DOMElement;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;

class ShipmentConfirmRequestBuilder extends RequestBuilder
{
    protected function buildShipment(array $data, DOMDocument $xml, DOMElement $parent)
    {
        /** @var Shipment $shipment */
        $shipment = $data['shipment'];

        $shipment->toXmlNode($xml, $parent);
    }

    protected function buildFunctionalityRequest(DOMDocument $xml, array $data)
    {
        $request = $this->createFunctionalityRequestNode($xml, 'ShipConfirmRequest', 'ShipConfirm');

        $this->buildShipment($data, $xml, $request);

        $request->appendChild($labelSpecification = $xml->createElement('LabelSpecification'));
        $labelSpecification->appendChild($labelImageFormat = $xml->createElement('LabelImageFormat'));
        $labelImageFormat->appendChild($xml->createElement('Code', 'EPL')); // TODO: Figure out which label should be used
    }
}
