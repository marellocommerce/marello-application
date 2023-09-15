<?php

namespace Marello\Bundle\ShippingBundle\Integration\Manual;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;

class ManualShippingServiceIntegration implements ShippingServiceIntegrationInterface
{
    public function __construct(
        protected ManagerRegistry $doctrine
    ) {
    }

    /**
     * @param ShippingAwareInterface $shippingAwareInterface
     * @param array $data
     *
     * @return Shipment
     * @throws ManualIntegrationException
     */
    public function createShipment(ShippingAwareInterface $shippingAwareInterface, array $data)
    {
        $shipment = new Shipment();

        $shipment->setShippingService('manual');
        $shippingAwareInterface->setShipment($shipment);
        
        $this->getShipmentManager()->persist($shipment);
        $this->getShipmentManager()->flush();

        return $shipment;
    }

    /**
     * @return ObjectManager|null|object
     */
    private function getShipmentManager()
    {
        return $this->doctrine->getManagerForClass(Shipment::class);
    }
}
