<?php

namespace Marello\Bundle\ShippingBundle\Integration\Manual;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;

class ManualShippingServiceIntegration implements ShippingServiceIntegrationInterface
{
    /** @var Registry */
    public $doctrine;

    /**
     * ManualShippingServiceIntegration constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(
        Registry $doctrine
    ) {
        $this->doctrine = $doctrine;
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
