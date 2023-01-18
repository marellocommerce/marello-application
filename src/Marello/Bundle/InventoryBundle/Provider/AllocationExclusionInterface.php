<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\Collection;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Allocation;

interface AllocationExclusionInterface
{
    public function getItems(Order $order, Allocation $allocation = null): Collection;
}
