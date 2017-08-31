<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\InventoryBundle\ImportExport\Strategy\InventoryLevelUpdateStrategy as BaseStrategy;

class InventoryLevelUpdateStrategy extends BaseStrategy
{
    /**
     * @param object|InventoryLevel $entity
     * @param bool                 $isFullData
     * @param bool                 $isPersistNew
     * @param null                 $itemData
     * @param array                $searchContext
     * @param bool                 $entityIsRelation
     *
     * @return null
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = [],
        $entityIsRelation = false
    ) {
        $entityClass = ClassUtils::getClass($entity);
        // find existing or new entity
        $existingEntity = $this->findInventoryLevel($entity);

        $operator = $this->getAdjustmentOperator($entity->getInventoryQty());
        $inventoryUpdateQty = $this->levelCalculator->calculateAdjustment($operator, $entity->getInventoryQty());
        $allocatedInventory = 0;
        $trigger = 'import';

        if ($existingEntity) {
            if (!$this->strategyHelper->isGranted("EDIT", $existingEntity)) {
                return $this->addAccessDeniedError($entityClass);
            }

            $this->checkEntityAcl($entity, $existingEntity, $itemData);
            $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
                $existingEntity,
                $existingEntity->getInventoryItem(),
                $inventoryUpdateQty,
                $allocatedInventory,
                $trigger
            );
            $canUpdate = true;
        } else {
            if (!$this->strategyHelper->isGranted("CREATE", $entity)) {
                $this->addAccessDeniedError($entityClass);
            }

            if ($warehouse = $this->getWarehouse($entity)) {
                $this->checkEntityAcl($entity, null, $itemData);
                $product = $this->getProduct($entity);
                $inventoryItem = $this->getInventoryItem($product);
                $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
                    $product,
                    $inventoryItem,
                    $inventoryUpdateQty,
                    $allocatedInventory,
                    $trigger
                );

                $warehouse = $this->getWarehouse($entity);
                $context->setValue('warehouse', $warehouse);
                $canUpdate = true;
            } else {
                $error[] = $this->translator->trans('marello.inventory.messages.error.warehouse.not_found');
                $this->strategyHelper->addValidationErrors($error, $this->context);

                return null;
            }
        }

        if ($canUpdate) {
            $this->eventDispatcher->dispatch(
                InventoryUpdateEvent::NAME,
                new InventoryUpdateEvent($context)
            );
        }

        // deliberately return a different entity than the initial imported entity,
        // during errors with multiple runs of import
        $product = $this->getProduct($entity);
        return $this->getInventoryItem($product);
    }
}
