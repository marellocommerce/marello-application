<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class WarehouseSystemGroupListener
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
        if ($this->installed) {
            $systemGroup = $args
                ->getEntityManager()
                ->getRepository(WarehouseGroup::class)
                ->findSystemWarehouseGroup();

            if ($systemGroup) {
                $warehouse->setGroup($systemGroup);
            }
        }
    }
}
