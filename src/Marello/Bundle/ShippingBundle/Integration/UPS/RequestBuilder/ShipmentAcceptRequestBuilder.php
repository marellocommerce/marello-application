<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;
use Marello\Bundle\ShippingBundle\Entity\Shipment;

class ShipmentAcceptRequestBuilder extends RequestBuilder
{
    /**
     * @param DOMDocument $xml
     * @param array       $data
     */
    protected function buildFunctionalityRequest(DOMDocument $xml, array $data)
    {
        $request = $this->createFunctionalityRequestNode($xml, 'ShipmentAcceptRequest', 'ShipAccept');

        $request->appendChild($this->createShipmentDigestNode($xml, $data['shipment']));
    }

    /**
     * @param DOMDocument $xml
     * @param Shipment    $shipment
     *
     * @return \DOMElement
     */
    private function createShipmentDigestNode(DOMDocument $xml, Shipment $shipment)
    {
        return $xml->createElement('ShipmentDigest', $shipment->getUpsShipmentDigest());
    }
}
