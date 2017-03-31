<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class LoadWarehouseData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        /*
         * Create default warehouse with name of Warehouse.
         */
        $defaultWarehouse = new Warehouse('Warehouse', true);
        $defaultWarehouse->setOwner($organization);

//        $warehouseAddress = new MarelloAddress();
//
//        $manager->persist($warehouseAddress);
//
//        $defaultWarehouse->setAddress($warehouseAddress);

        $manager->persist($defaultWarehouse);
        $manager->flush();
    }
}
