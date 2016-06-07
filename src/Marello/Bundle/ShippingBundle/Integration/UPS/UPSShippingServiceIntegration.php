<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentConfirmRequestBuilder;
use SimpleXMLElement;

class UPSShippingServiceIntegration implements ShippingServiceIntegrationInterface
{
    /** @var UPSApi */
    protected $api;

    /** @var ShipmentConfirmRequestBuilder */
    protected $shipmentConfirmRequestBuilder;

    /**
     * UPSShippingServiceIntegration constructor.
     *
     * @param UPSApi                        $api
     * @param ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder
     */
    public function __construct(UPSApi $api, ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder)
    {
        $this->api                           = $api;
        $this->shipmentConfirmRequestBuilder = $shipmentConfirmRequestBuilder;
    }

    /**
     * @param array $data
     *
     * @return Shipment
     *
     * @throws UPSIntegrationException
     */
    public function requestShipment(array $data)
    {
        $request = $this->shipmentConfirmRequestBuilder->build($data);

        $response = $this->api->post('ShipConfirm', $request);

        $result = new SimpleXMLElement($response);

        $error = $result->xpath('/ShipmentConfirmResponse/Response/Error');

        if (!empty($error)) {
            /** @var SimpleXMLElement $error */
            $error = reset($error);

            $exception = new UPSIntegrationException(
                (string)$error->ErrorDescription,
                (string)$error->ErrorCode
            );

            throw $exception->setRawResponse($response);
        }

        return $this->handleShipmentConfirmResponse($result);
    }

    protected function handleShipmentConfirmResponse(SimpleXMLElement $result)
    {
        $shipment = new Shipment();
        $shipment->setShippingService('ups');

        $digest = $result->xpath('/ShipConfirmResponse/ShipmentResults/ShipmentDigest');
        $digest = reset($digest);
        $digest = (string)$digest;

        $shipment->setUpsShipmentDigest($digest);

        return $shipment;
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
