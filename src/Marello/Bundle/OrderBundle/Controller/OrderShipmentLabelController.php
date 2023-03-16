<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\GaufretteBundle\FileManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderShipmentLabelController extends AbstractController
{
    /**
     * @Route(
     *     path="/{id}/shipment-label/download",
     *     name="marello_order_shipment_label_download"
     * )
     */
    public function downloadAction(Order $order): BinaryFileResponse
    {
        $shipment = $order->getShipment();
        if (!$shipment || !$shipment->getBase64EncodedLabel()) {
            throw new \LogicException('The order must have a shipment label to download');
        }

        /** @var FileManager $fileManager */
        $fileManager = $this->container->get(FileManager::class);
        $file = $fileManager->writeToTemporaryFile(base64_decode($shipment->getBase64EncodedLabel()));

        $response = new BinaryFileResponse($file->getPathname());
        $response->headers->set('Cache-Control', 'public');
        $response->headers->set(
            'Content-Type',
            $file->getMimeType() ?? 'application/octet-stream'
        );
        $response->headers->set('Content-Disposition', 'attachment; filename="label_' . $order->getId() . '"');

        return $response;
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                FileManager::class,
            ]
        );
    }
}
