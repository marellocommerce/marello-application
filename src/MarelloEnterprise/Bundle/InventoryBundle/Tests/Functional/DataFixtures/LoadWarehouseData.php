<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class LoadWarehouseData extends AbstractFixture
{
    CONST ADDITIONAL_WAREHOUSE = 'additional';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        /*
         * Create default warehouse with name of Warehouse.
         */
        $additionalWarehouse = new Warehouse('Warehouse 13', false);
        $additionalWarehouse->setOwner($organization);

        $warehouseAddress = new MarelloAddress();

        $manager->persist($warehouseAddress);

        $additionalWarehouse->setAddress($warehouseAddress);

        $manager->persist($additionalWarehouse);
        $manager->flush();

        $this->setReference(self::ADDITIONAL_WAREHOUSE, $additionalWarehouse);
    }
}
