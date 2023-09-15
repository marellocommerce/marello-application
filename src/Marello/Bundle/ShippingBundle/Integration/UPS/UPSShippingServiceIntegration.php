<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Entity\TrackingInfo;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentAcceptRequestBuilder;
use Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder\ShipmentConfirmRequestBuilder;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethod;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\File\File as SFFile;

class UPSShippingServiceIntegration implements ShippingServiceIntegrationInterface
{
    public function __construct(
        protected UPSApi $api,
        protected ManagerRegistry $doctrine,
        protected FileManager $fileManager,
        protected ShipmentConfirmRequestBuilder $shipmentConfirmRequestBuilder,
        protected ShipmentAcceptRequestBuilder $shipmentAcceptRequestBuilder
    ) {
    }

    /**
     * @param ShippingAwareInterface $shippingAwareInterface
     * @param array $data
     *
     * @return Shipment
     * @throws UPSIntegrationException
     */
    public function createShipment(ShippingAwareInterface $shippingAwareInterface, array $data)
    {
        $shipment = new Shipment();

        $this->confirmShipment($shipment, $shippingAwareInterface, $data);

        $this->acceptShipment($shipment);

        $this->createTrackingInfo($shipment);

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
        $statusCode = $result->xpath("/{$responseType}/Response/ResponseStatusCode");
        $statusCode = reset($statusCode);
        $statusCode = (string)$statusCode;

        /*
         * If response status is "1" do nothing (1 means success).
         */
        if ($statusCode === '1') {
            return;
        }

        $errors = $result->xpath("/{$responseType}/Response/Error");

        foreach ($errors as $error) {
            $severity = (string)$error->ErrorSeverity;

            if ($severity !== 'Warning') {
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
     * @param ShippingAwareInterface    $shippingAwareInterface
     * @param array    $data
     *
     * @throws UPSIntegrationException
     */
    private function confirmShipment(Shipment $shipment, ShippingAwareInterface $shippingAwareInterface, array $data)
    {
        $request  = $this->shipmentConfirmRequestBuilder->build($data);
        $response = $this->api->post('ShipConfirm', $request);
        $result   = new SimpleXMLElement($response);

        /*
         * Handle any errors returned in response.
         */
        $this->handelError($result, 'ShipmentConfirmResponse', $response);

        $shipment->setShippingService('ups');
        $shippingAwareInterface->setShipment($shipment);

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

        $shipment->setUpsPackageTrackingNumber($tracking);
        $shipment->setIdentificationNumber($idNo);

        $image = $result->xpath('/ShipmentAcceptResponse/ShipmentResults/PackageResults/LabelImage/GraphicImage');
        $image = reset($image);
        $image = (string)$image;

        $shipment->setBase64EncodedLabel($image);

        $file = $this->prepareLabelFile($image);

        $shipment->setShippingLabel($file);
    }

    private function createTrackingInfo(Shipment $shipment): void
    {
        $trackingInfo = new TrackingInfo();
        $shipment->setTrackingInfo($trackingInfo);
        $trackingInfo->setShipment($shipment);
        $trackingInfo->setOrder($shipment->getOrder());
        $trackingInfo->setProvider('ups');
        $trackingInfo->setProviderName('ups'); // TODO: check where we can get it from provider
        $trackingInfo->setTrackingCode($shipment->getUpsPackageTrackingNumber());
        // TODO find the way how to use UPSShippingMethod
        $trackingInfo->setTrackingUrl(UPSShippingMethod::TRACKING_URL);
        $trackingInfo->setTrackTraceUrl(UPSShippingMethod::TRACKING_URL . $shipment->getUpsPackageTrackingNumber());
    }

    /**
     * @param string   $image
     *
     * @return File
     */
    private function prepareLabelFile($image)
    {
        /*
         * Create a temporary file and put decoded content in it ...
         */
        $tempFile = tempnam(sys_get_temp_dir(), 'ups-shipping-label-');
        file_put_contents($tempFile, base64_decode($image));

        /*
         * Prepare symfony file for handling with oro attachment bundle ...
         */
        $sfFile = new SFFile($tempFile);
        $file = new File();
        $file->setFile($sfFile);

        $this->fileManager->preUpload($file);
        $this->fileManager->upload($file);

        /*
         * Delete temporary file ...
         */
        unlink($tempFile);

        return $file;
    }
}
