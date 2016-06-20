<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;


use DOMDocument;
use Marello\Bundle\ShippingBundle\Entity\Shipment;

class ShipmentAcceptRequestBuilder extends RequestBuilder
{

    protected function buildFunctionalityRequest(DOMDocument $xml, array $data)
    {
        $request = $this->createFunctionalityRequestNode($xml, 'ShipmentAcceptRequest', 'ShipAccept');

        $request->appendChild($this->createShipmentDigestNode($xml, $data['shipment']));
    }

    private function createShipmentDigestNode(DOMDocument $xml, Shipment $shipment)
    {
        return $xml->createElement('ShipmentDigest', $shipment->getUpsShipmentDigest());
    }
}
