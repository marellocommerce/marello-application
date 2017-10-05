<?php

namespace Marello\Bundle\ShipmentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class UpdateCurrentShipmentsWithOrganization extends AbstractFixture
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCurrentShipments();
    }

    /**
     * update current Shipments with organization
     */
    public function updateCurrentShipments()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $shipments = $this->manager
            ->getRepository('MarelloShippingBundle:Shipment')
            ->findBy(['organization' => null]);
        foreach ($shipments as $shipment) {
            $shipment->setOrganization($organization);
            $this->manager->persist($shipment);
        }
        $this->manager->flush();
    }
}
