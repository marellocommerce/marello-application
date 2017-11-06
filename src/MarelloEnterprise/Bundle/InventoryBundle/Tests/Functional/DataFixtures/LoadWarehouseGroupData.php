<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
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
        /** @var Warehouse $warehouse */
        $warehouse = $this->getReference(LoadWarehouseData::WAREHOUSE_1_REF);

        $additionalWarehouseGroup = new WarehouseGroup();
        $additionalWarehouseGroup
            ->setOrganization($organization)
            ->setName('additionalGroup')
            ->setSystem(false)
            ->addWarehouse($warehouse);
        
        $manager->persist($additionalWarehouseGroup);
        $this->setReference(self::ADDITIONAL_WAREHOUSE_GROUP, $additionalWarehouseGroup);
        $manager->flush();
    }
}
