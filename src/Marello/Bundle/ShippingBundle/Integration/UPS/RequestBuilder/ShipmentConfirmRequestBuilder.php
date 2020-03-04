<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;

class ShipmentConfirmRequestBuilder extends RequestBuilder
{

    /**
     * @param DOMDocument $xml
     * @param array       $data
     */
    protected function buildFunctionalityRequest(DOMDocument $xml, array $data)
    {
        $request = $this->createFunctionalityRequestNode($xml, 'ShipmentConfirmRequest', 'ShipConfirm');

        $request->appendChild($this->createLabelSpecificationNode($xml));
        $request->appendChild($this->createShipmentNode($data, $xml));
    }

    /**
     * @param array       $data
     * @param DOMDocument $xml
     *
     * @return \DOMElement
     */
    protected function createShipmentNode(array $data, DOMDocument $xml)
    {
        /** @var Shipment $shipment */
        $shipment = $data['shipment'];

        return $shipment->toXmlNode($xml);
    }

    /**
     * @param DOMDocument $xml
     *
     * @return \DOMElement
     */
    protected function createLabelSpecificationNode(DOMDocument $xml)
    {
        $labelSpecification = $xml->createElement('LabelSpecification');

        $labelSpecification->appendChild($labelPrintMethod = $xml->createElement('LabelPrintMethod'));

        $labelPrintMethod->appendChild($xml->createElement('Code', 'GIF'));
        $labelPrintMethod->appendChild($xml->createElement('Description', 'gif file'));

        $labelSpecification->appendChild($xml->createElement('HTTPUserAgent', 'Mozilla/4.5'));

        $labelSpecification->appendChild($labelImageFormat = $xml->createElement('LabelImageFormat'));

        $labelImageFormat->appendChild($xml->createElement('Code', 'GIF'));
        $labelImageFormat->appendChild($xml->createElement('Description', 'gif'));

        return $labelSpecification;
    }
}
