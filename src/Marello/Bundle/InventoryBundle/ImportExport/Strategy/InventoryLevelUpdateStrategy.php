<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryLevelUpdateStrategy extends ConfigurableAddOrReplaceStrategy
{
    const IMPORT_TRIGGER = 'import';
    const ALLOCATED_QTY = 0;

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
        $inventoryUpdateQty = $entity->getInventoryQty();
        // find existing or new entity
        $existingEntity = $this->findInventoryLevel($entity);
        if ($existingEntity) {
            $this->checkEntityAcl($entity, $existingEntity, $itemData);
            $batchInventory = $existingEntity->getInventoryItem()->isEnableBatchInventory();
            $inventoryBatches = $entity->getInventoryBatches();
            if (!$batchInventory || $inventoryBatches->count() === 0) {
                $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
                    $existingEntity,
                    $existingEntity->getInventoryItem(),
                    [],
                    $inventoryUpdateQty,
                    self::ALLOCATED_QTY,
                    self::IMPORT_TRIGGER
                );
            } else {
                foreach ($inventoryBatches as $inventoryBatch) {
                    $inventoryBatch->setInventoryLevel($existingEntity);
                    $existingInventoryBatch = $this->findInventoryBatch($inventoryBatch);
                    if ($existingInventoryBatch) {
                        if ($inventoryBatch->getPurchasePrice() != null) {
                            $existingInventoryBatch->setPurchasePrice($inventoryBatch->getPurchasePrice());
                        }
                        if ($inventoryBatch->getExpirationDate() != null) {
                            $existingInventoryBatch->setExpirationDate($inventoryBatch->getExpirationDate());
                        }
                        $inventoryBatch = $existingInventoryBatch;
                    }
                    $batches = [
                        [
                            'batch' => $inventoryBatch,
                            'qty' => $inventoryUpdateQty,
                        ]
                    ];
                    $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
                        $existingEntity,
                        $existingEntity->getInventoryItem(),
                        $batches,
                        $inventoryUpdateQty,
                        self::ALLOCATED_QTY,
                        self::IMPORT_TRIGGER
                    );
                }
            }
        } else {
            $context = $this->createNewEntityContext($entity, $itemData);
        }

        if (!$this->context->getErrors()) {
            $this->eventDispatcher->dispatch(
                new InventoryUpdateEvent($context),
                InventoryUpdateEvent::NAME
            );
        }

        // deliberately return a different entity than the initial imported entity,
        // during errors with multiple runs of import
        $product = $this->getProduct($entity);
        if (!$product) {
            return null;
        }

        return $this->getInventoryItem($product);
    }

    /**
     * @param object|InventoryLevel $entity
     * @param $itemData
     * @return InventoryUpdateContext|null
     */
    protected function createNewEntityContext($entity, $itemData)
    {
        if ($warehouse = $this->getWarehouse($entity)) {
            $this->checkEntityAcl($entity, null, $itemData);
            $product = $this->getProduct($entity);
            if (!$product) {
                return $this->addProductNotExistValidationError($itemData);
            }
            $inventoryItem = $this->getInventoryItem($product);
            $inventoryUpdateQty = $entity->getInventoryQty();

            $newEntityKey = $this->createSerializedEntityKey(
                $entity,
                $entity->getInventoryItem(),
                $warehouse->getCode()
            );
            if ($this->newEntitiesHelper->getEntity($newEntityKey)) {
                return $this->addDuplicateValidationError($itemData);
            }

            $this->newEntitiesHelper->setEntity($newEntityKey, $entity);
            $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
                $product,
                $inventoryItem,
                $inventoryUpdateQty,
                self::ALLOCATED_QTY,
                self::IMPORT_TRIGGER
            );
        } else {
            $error[] = $this->translator->trans('marello.inventory.messages.error.warehouse.not_found');
            $this->strategyHelper->addValidationErrors($error, $this->context);

            return null;
        }

        return $context;
    }

    /**
     * Create serialized key for identifying unique items
     * @param $entity
     * @param $inventoryItemId
     * @param $warerhouseCode
     * @return string
     */
    private function createSerializedEntityKey($entity, $inventoryItemId, $warerhouseCode)
    {
        $entityClass = ClassUtils::getClass($entity);
        return sprintf('%s:%s', $entityClass, serialize([$inventoryItemId, $warerhouseCode]));
    }

    /**
     * @param $itemData
     * @return null
     */
    protected function addDuplicateValidationError(array $itemData)
    {
        $error = $this->translator->trans(
            'marello.inventory.messages.error.inventorylevel.duplicate_entry',
            ['%entity_sku%' => $itemData['inventoryItem']['product']['sku']]
        );

        $this->context->addError($error);

        return null;
    }

    /**
     * @param $itemData
     * @return null
     */
    protected function addProductNotExistValidationError(array $itemData)
    {
        $error[] = $this->translator->trans(
            'marello.inventory.messages.error.inventorylevel.product_not_found',
            ['%entity_sku%' => $itemData['inventoryItem']['product']['sku']]
        );
        $this->strategyHelper->addValidationErrors($error, $this->context);

        return null;
    }

    /**
     * @param InventoryLevel $entity
     *
     * @return null|object|InventoryLevel
     */
    protected function findInventoryLevel($entity)
    {
        /** @var Product $product */
        $product = $this->getProduct($entity);
        if (!$product) {
            return null;
        }

        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->getInventoryItem($product);
        if (!$inventoryItem) {
            return null;
        }

        $warehouse = $this->getWarehouse($entity);
        if (!$warehouse) {
            return null;
        }

        /** @var InventoryLevel $level */
        $level = $this->databaseHelper->findOneBy(InventoryLevel::class, [
            'inventoryItem' => $inventoryItem->getId(),
            'warehouse'     => $warehouse->getId()
        ]);

        return $level;
    }

    /**
     * @param InventoryBatch $entity
     *
     * @return null|object|InventoryBatch
     */
    protected function findInventoryBatch(InventoryBatch $entity)
    {
        if (!$entity->getBatchNumber()) {
            return null;
        }
        $invLevel = $entity->getInventoryLevel();
        if (!$invLevel->getId()) {
            return null;
        }

        return $this->databaseHelper->findOneBy(InventoryBatch::class, [
            'inventoryLevel' => $invLevel->getId(),
            'batchNumber'    => $entity->getBatchNumber()
        ]);
    }

    /**
     * @param InventoryLevel $entity
     * @return null|object|Product
     */
    protected function getProduct($entity)
    {
        return $this->databaseHelper
            ->findOneBy(
                Product::class,
                [
                    'sku' => $entity->getInventoryItem()->getProduct()->getSku(),
                    'organization' => $entity->getOrganization()
                ]
            );
    }

    /**
     * @param Product $entity
     * @return null|object|InventoryItem
     */
    protected function getInventoryItem($entity)
    {
        return $this->databaseHelper->findOneBy(InventoryItem::class, ['product' => $entity->getId()]);
    }

    /**
     * @param InventoryLevel $entity
     * @return null|object|Warehouse
     */
    protected function getWarehouse($entity)
    {
        return $this->databaseHelper->findOneBy(Warehouse::class, ['code' => $entity->getWarehouse()->getCode()]);
    }

    /**
     * Increment context counters.
     *
     * @param $entity
     */
    protected function updateContextCounters($entity)
    {
        $identifier = $this->databaseHelper->getIdentifier($entity);
        if ($identifier) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }
    }
}
