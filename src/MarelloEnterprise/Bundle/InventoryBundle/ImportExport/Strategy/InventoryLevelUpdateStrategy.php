<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy as BaseStrategy;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryLevelUpdateStrategy extends BaseStrategy
{
    /**
     * @param object|InventoryLevel $entity
     * @param $itemData
     * @return \Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext|null
     */
    protected function createNewEntityContext($entity, $itemData)
    {
        $context = parent::createNewEntityContext($entity, $itemData);

        if ($context) {
            $warehouse = $this->getWarehouse($entity);
            $context->setValue('warehouse', $warehouse);
        }

        return $context;
    }
}
