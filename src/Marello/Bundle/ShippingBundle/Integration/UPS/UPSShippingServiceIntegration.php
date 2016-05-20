<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;

class UPSShippingServiceIntegration implements ShippingServiceIntegrationInterface
{
    /** @var UPSApi */
    protected $api;

    /**
     * UPSShippingServiceIntegration constructor.
     *
     * @param UPSApi $api
     */
    public function __construct(UPSApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param array $data
     *
     * @return Shipment
     */
    public function requestShipment(array $data)
    {
        // TODO: Implement requestShipment() method.
    }

    /**
     * @param Shipment $shipment
     *
     * @return mixed
     */
    public function confirmShipment(Shipment $shipment)
    {
        // TODO: Implement confirmShipment() method.
    }
}
