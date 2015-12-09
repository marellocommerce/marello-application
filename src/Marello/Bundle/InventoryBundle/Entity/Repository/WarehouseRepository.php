<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseRepository extends EntityRepository
{
    /**
     * Finds default warehouse.
     *
     * @return Warehouse
     */
    public function getDefault()
    {
        return $this->findOneBy(['default' => true]);
    }
}
