<?php

namespace Marello\Bundle\UPSBundle\Model\Response;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

class ShipmentConfirmResponse extends AbstractUPSXMLResponse
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
        $this->handelError($result, 'ShipmentConfirmResponse');
        $this->result = $result;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getShipmentDigest()
    {
        return (string)$this->getElement($this->result, '/ShipmentConfirmResponse/ShipmentDigest');
    }
}
