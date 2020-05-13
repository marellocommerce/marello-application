<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryLevelFinishFormViewEvent;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class InventoryLevelFormViewEventListener
{
    public function onFinishView(InventoryLevelFinishFormViewEvent $event)
    {
        $view = $event->getView();
        $inventoryLevel = $view->vars['value'];
        if ($inventoryLevel instanceof InventoryLevel) {
            $warehouseType = $inventoryLevel->getWarehouse()->getWarehouseType();
            if ($warehouseType->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                $view->vars['allow_delete'] = false;
            }
        }
    }
}
