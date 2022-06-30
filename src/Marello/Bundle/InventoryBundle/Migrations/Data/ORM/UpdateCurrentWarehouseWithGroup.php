<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateCurrentWarehouseWithGroup extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadWarehouseGroupData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCurrentWarehouse();
    }

    /**
     * update current Warehouse with WarehouseGroup
     */
    public function updateCurrentWarehouse()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $defaultWarehouse = $this->manager->getRepository(Warehouse::class)->getDefault($aclHelper);
        /** @var WarehouseGroup $warehouseGroup */
        $warehouseGroup = $this->getReference('warehouse_system_group');
        $defaultWarehouse->setGroup($warehouseGroup);
        $this->manager->persist($defaultWarehouse);
        $this->manager->flush();
    }
}
