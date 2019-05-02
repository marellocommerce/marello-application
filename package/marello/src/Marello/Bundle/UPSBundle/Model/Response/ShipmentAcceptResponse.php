<?php

namespace Marello\Bundle\UPSBundle\Model\Response;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

class ShipmentAcceptResponse extends AbstractUPSXMLResponse
{
    /**
     * @var \SimpleXMLElement
     */
    private $result;

    /**
     * {@inheritdoc}
     */
    public function parse(RestResponseInterface $restResponse)
    {
        $result = new \SimpleXMLElement($restResponse->getBodyAsString());
        $this->handelError($result, 'ShipmentAcceptResponse');
        $this->result = $result;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return (string)$this->getElement(
            $this->result,
            '/ShipmentAcceptResponse/ShipmentResults/PackageResults/TrackingNumber'
        );
    }

    /**
     * @return string
     */
    public function getShipmentIdentificationNumber()
    {
        return (string)$this->getElement(
            $this->result,
            '/ShipmentAcceptResponse/ShipmentResults/ShipmentIdentificationNumber'
        );
    }

    /**
     * @return string
     */
    public function getGraphicImage()
    {
        return (string)$this->getElement(
            $this->result,
            '/ShipmentAcceptResponse/ShipmentResults/PackageResults/LabelImage/GraphicImage'
        );
    }
}
