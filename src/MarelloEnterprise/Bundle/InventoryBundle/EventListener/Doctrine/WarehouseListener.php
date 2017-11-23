<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class WarehouseListener
{
    /**
     * Installed flag
     *
     * @var bool
     */
    protected $installed;

    /**
     * @param bool $installed
     */
    public function __construct($installed)
    {
        $this->installed = $installed;
    }

    /**
     * @param Warehouse $warehouse
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Warehouse $warehouse, LifecycleEventArgs $args)
    {
        if ($this->installed && !$warehouse->getGroup()) {
            $em = $args->getEntityManager();
            $whType = $warehouse->getWarehouseType();
            if ($whType && $whType->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED) {
                $group = new WarehouseGroup();
                $group
                    ->setName($warehouse->getLabel())
                    ->setOrganization($warehouse->getOwner())
                    ->setDescription(sprintf('%s group', $warehouse->getLabel()))
                    ->setSystem(false);
                $em->persist($group);
                $em->flush($group);
            } else {
                $group = $em
                    ->getRepository(WarehouseGroup::class)
                    ->findSystemWarehouseGroup();
            }
            if ($group) {
                $warehouse->setGroup($group);
            }
        }
    }
}
