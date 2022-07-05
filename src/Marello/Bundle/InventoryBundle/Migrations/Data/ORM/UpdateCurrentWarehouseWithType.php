<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateCurrentWarehouseWithType extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
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
            LoadWarehouseTypeData::class
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
     * update current Warehouse with the WarehouseType
     */
    public function updateCurrentWarehouse()
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $defaultWarehouse = $this->manager->getRepository(Warehouse::class)->getDefault($aclHelper);
        /** @var WarehouseType $warehouseType */
        $warehouseType = $this->getReference('warehouse_type_global');
        $defaultWarehouse->setWarehouseType($warehouseType);
        $this->manager->persist($defaultWarehouse);
        $this->manager->flush();
    }
}
