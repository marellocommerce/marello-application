<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class LoadWarehouseGroupData extends AbstractFixture implements DependentFixtureInterface
{
    const ADDITIONAL_WAREHOUSE_GROUP = 'additional_group';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadWarehouseData::class
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        /*
         * Create default warehouse with name of Warehouse.
         */
        $additionalWarehouseGroup = new WarehouseGroup();
        $additionalWarehouseGroup
            ->setOrganization($organization)
            ->setName('additionalGroup')
            ->setSystem(false)
            ->addWarehouse($this->getReference(LoadWarehouseData::WAREHOUSE_1_REF));

        $manager->persist($additionalWarehouseGroup);
        $manager->flush();

        $this->setReference(self::ADDITIONAL_WAREHOUSE_GROUP, $additionalWarehouseGroup);
    }
}
