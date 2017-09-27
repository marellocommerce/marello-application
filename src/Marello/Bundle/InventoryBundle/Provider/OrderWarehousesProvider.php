<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order)
    {
        return [
            0 => new OrderWarehouseResult([
                OrderWarehouseResult::WAREHOUSE_FIELD => $this->getWarehouse(),
                OrderWarehouseResult::ORDER_ITEMS_FIELD => $order->getItems(),
            ])
        ];
    }
    
    /**
     * @return Warehouse
     */
    protected function getWarehouse()
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(Warehouse::class)
            ->getRepository(Warehouse::class)
            ->getDefault();
    }
}
