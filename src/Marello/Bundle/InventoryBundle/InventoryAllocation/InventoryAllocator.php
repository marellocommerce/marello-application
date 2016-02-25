<?php

namespace Marello\Bundle\InventoryBundle\InventoryAllocation;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryAllocation;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Component\PropertyAccess\PropertyAccessor;

class InventoryAllocator
{
    /** @var Registry */
    protected $doctrine;

    /**
     * InventoryAllocator constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Allocates inventory to given target.
     * Also modifies inventory item.
     *
     * @param InventoryItem             $item
     * @param int                       $amount
     * @param AllocationTargetInterface $target Target entity
     */
    public function allocate(InventoryItem $item, $amount, AllocationTargetInterface $target)
    {
        $allocation = new InventoryAllocation($item, $amount);
        $this->setAllocationTarget($allocation, $target);

        $this->manager()->persist($allocation);
    }

    /**
     * Deallocates inventory.
     * Also modifies inventory item.
     *
     * @param InventoryAllocation $allocation
     */
    public function deallocate(InventoryAllocation $allocation)
    {
        $this->manager()->remove($allocation);
    }

    /**
     * Sets allocation target on allocation entity.
     *
     * @param InventoryAllocation       $allocation
     * @param AllocationTargetInterface $target
     */
    protected function setAllocationTarget(InventoryAllocation $allocation, AllocationTargetInterface $target)
    {
        $pa = new PropertyAccessor();

        $pa->setValue(
            $allocation,
            sprintf('target%s', ucfirst($target->getAllocationPropertyName())),
            $target
        );
    }

    /**
     * @return EntityManager
     */
    protected function manager()
    {
        return $this->doctrine
            ->getManager();
    }
}
