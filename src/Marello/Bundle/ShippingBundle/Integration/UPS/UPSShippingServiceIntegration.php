<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentAcceptRequestBuilder;
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

    /** @var ShipmentAcceptRequestBuilder */
    protected $shipmentAcceptRequestBuilder;

    /**
     * UPSShippingServiceIntegration constructor.
     *
     * @param UPSApi                        $api
     * @param Registry                      $doctrine
     * @param ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder
     * @param ShipmentAcceptRequestBuilder  $shipmentAcceptRequestBuilder
     */
    public function __construct(
        UPSApi $api,
        Registry $doctrine,
        ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder,
        ShipmentAcceptRequestBuilder $shipmentAcceptRequestBuilder
    ) {
        $this->api                           = $api;
        $this->shipmentConfirmRequestBuilder = $shipmentConfirmRequestBuilder;
        $this->doctrine                      = $doctrine;
        $this->shipmentAcceptRequestBuilder  = $shipmentAcceptRequestBuilder;
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return Shipment
     * @throws UPSIntegrationException
     */
    public function createShipment(Order $order, array $data)
    {
        $shipment = new Shipment();

        $this->confirmShipment($shipment, $order, $data);

        $this->acceptShipment($shipment);

        $this->getShipmentManager()->persist($shipment);
        $this->getShipmentManager()->flush();

        return $shipment;
    }

    /**
     * @param SimpleXMLElement $result
     * @param string           $responseType
     * @param string           $rawResponse
     *
     * @throws UPSIntegrationException
     */
    protected function handelError(SimpleXMLElement $result, $responseType, $rawResponse)
    {
        $statusCode = (string)$result->xpath("/{$responseType}/Response/ResponseStatusCode");

        /*
         * If response status is "1" do nothing (1 means success).
         */
        if ($statusCode === '1') {
            return;
        }

        $errors = $result->xpath("/{$responseType}/Response/Error");

        foreach ($errors as $error) {
            $severity = (string)$error->ErrorSeverity;

            if ($severity === 'Error') {
                $exception = new UPSIntegrationException(
                    (string)$error->ErrorDescription,
                    (string)$error->ErrorCode
                );

                throw $exception->setRawResponse($rawResponse);
            } else {
                /*
                 * TODO: Log Warning
                 */
            }
        }
    }

    /**
     * @return ObjectManager|null|object
     */
    private function getShipmentManager()
    {
        return $this->doctrine->getManagerForClass(Shipment::class);
    }

    /**
     * @param Shipment $shipment
     * @param Order    $order
     * @param array    $data
     *
     * @throws UPSIntegrationException
     */
    private function confirmShipment(Shipment $shipment, Order $order, array $data)
    {
        $request  = $this->shipmentConfirmRequestBuilder->build($data);
        $response = $this->api->post('ShipConfirm', $request);
        $result   = new SimpleXMLElement($response);

        /*
         * Handle any errors returned in response.
         */
        $this->handelError($result, 'ShipmentConfirmResponse', $response);

        $shipment->setShippingService('ups');
        $shipment->setOrder($order->setShipment($shipment));

        $digest = $result->xpath('/ShipmentConfirmResponse/ShipmentDigest');
        $digest = reset($digest);
        $digest = (string)$digest;

        $shipment->setUpsShipmentDigest($digest);
    }

    /**
     * @param Shipment $shipment
     *
     * @throws UPSIntegrationException
     */
    private function acceptShipment(Shipment $shipment)
    {
        $request  = $this->shipmentAcceptRequestBuilder->build(compact('shipment'));
        $response = $this->api->post('ShipAccept', $request);
        $result   = new SimpleXMLElement($response);

        /*
         * Handle any errors returned in response.
         */
        $this->handelError($result, 'ShipmentAcceptResponse', $response);

        $tracking = $result->xpath('/ShipmentAcceptResponse/ShipmentResults/PackageResults/TrackingNumber');
        $tracking = reset($tracking);
        $tracking = (string)$tracking;

        $idNo = $result->xpath('/ShipmentAcceptResponse/ShipmentResults/ShipmentIdentificationNumber');
        $idNo = reset($idNo);
        $idNo = (string)$idNo;

        $pickup = $result->xpath('/ShipmentAcceptResponse/ShipmentResults/PackageResults/PickupRequestNumber');
        $pickup = reset($pickup);
        $pickup = (string)$pickup;

        $shipment->setUpsPackageTrackingNumber($tracking);
        $shipment->setIdentificationNumber($idNo);
        $shipment->setPickupRequestNumber($pickup);
    }
}
