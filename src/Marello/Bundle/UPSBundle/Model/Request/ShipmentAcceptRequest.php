<?php

namespace Marello\Bundle\UPSBundle\Model\Request;

class ShipmentAcceptRequest extends AbstractUPSRequest
{
    /**
     * @var string
     */
    protected $shipmentDigest;

    /**
     * {@inheritdoc}
     */
    public function stringify()
    {
        // Create AccessRequest XMl
        $accessRequestXML = new \SimpleXMLElement("<AccessRequest></AccessRequest>");
        $accessRequestXML->addChild("AccessLicenseNumber", $this->getAccessLicenseNumber());
        $accessRequestXML->addChild("UserId", $this->getUsername());
        $accessRequestXML->addChild("Password", $this->getPassword());

        // Create ShipmentAcceptRequest XMl
        $shipmentAcceptRequestXML = new \SimpleXMLElement("<ShipmentAcceptRequest ></ShipmentAcceptRequest >");
        $request = $shipmentAcceptRequestXML->addChild('Request');
        $request->addChild("RequestAction", "01");

        $shipmentAcceptRequestXML->addChild("ShipmentDigest", $this->getShipmentDigest());

        return $accessRequestXML->asXML() . $shipmentAcceptRequestXML->asXML();
    }

    /**
     * @param string $shipmentDigest
     */
    public function setShipmentDigest($shipmentDigest)
    {
        $this->shipmentDigest = $shipmentDigest;
    }

    /**
     * @return string
     */
    public function getShipmentDigest()
    {
        return $this->shipmentDigest;
    }
}
