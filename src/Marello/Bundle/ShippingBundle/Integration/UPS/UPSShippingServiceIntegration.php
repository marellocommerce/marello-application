<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\OrderBundle\Entity\Order;
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

    /** @var Registry */
    public $doctrine;

    /**
     * UPSShippingServiceIntegration constructor.
     *
     * @param UPSApi                        $api
     * @param Registry                      $doctrine
     * @param ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder
     */
    public function __construct(
        UPSApi $api,
        Registry $doctrine,
        ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder
    ) {
        $this->api                           = $api;
        $this->shipmentConfirmRequestBuilder = $shipmentConfirmRequestBuilder;
        $this->doctrine                      = $doctrine;
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return Shipment
     * @throws UPSIntegrationException
     */
    public function requestShipment(Order $order, array $data)
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

        $shipment = $this->handleShipmentConfirmResponse($result);
        $shipment->setOrder($order);
        $order->setShipment($shipment);

        $manager = $this->doctrine->getManagerForClass(Shipment::class);
        $manager->persist($shipment);
        $manager->flush();

        return $shipment;
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
        $this->api->post('ShipConfirm', ''); // TODO: Fill ship confirm request
    }
}
