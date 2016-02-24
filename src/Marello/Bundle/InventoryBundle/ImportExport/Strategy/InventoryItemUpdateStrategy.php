<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Logging\InventoryLogger;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InventoryItemUpdateStrategy extends ConfigurableAddOrReplaceStrategy
{
    /** @var InventoryLogger */
    protected $inventoryLogger;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ImportStrategyHelper $strategyHelper,
        FieldHelper $fieldHelper,
        DatabaseHelper $databaseHelper,
        InventoryLogger $inventoryLogger
    ) {
        parent::__construct($eventDispatcher, $strategyHelper, $fieldHelper, $databaseHelper);

        $this->inventoryLogger = $inventoryLogger;
    }

    /**
     * @param object|InventoryItem $entity
     * @param bool                 $isFullData
     * @param bool                 $isPersistNew
     * @param mixed|array|null     $itemData
     * @param array                $searchContext
     *
     * @return null|object
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = []
    ) {
        $oid = spl_object_hash($entity);
        if (isset($this->cachedEntities[$oid])) {
            return $entity;
        }

        // find and cache existing entity
        $entityId = $this->getInventoryItemIdByProduct($entity->getProduct());

        // no entity inventory entity id found for this product,
        // we cannot update inventory for products which don't exist
        if (!$entityId) {
            return null;
        }

        // set entity id on entity for finding inventory item
        $entity->setId($entityId);
        $existingEntity = $this->findExistingEntity($entity, $searchContext);

        if ($existingEntity) {
            $existingOid = spl_object_hash($existingEntity);
            if (isset($this->cachedEntities[$existingOid])) {
                return $existingEntity;
            }
            $this->cachedEntities[$existingOid] = $existingEntity;
        } else {
            // if can't find entity and new entity can't be persisted
            if (!$isPersistNew) {
                return null;
            }
            $this->databaseHelper->resetIdentifier($entity);
            $this->cachedEntities[$oid] = $entity;

            $this->inventoryLogger->directLog(
                $entity,
                'import',
                function (InventoryLog $log) use ($entity) {
                    $log->setOldQuantity(0);
                    $log->setOldAllocatedQuantity(0);
                }
            );
        }


        // update relations
        if ($isFullData) {
            $this->updateRelations($entity, $itemData);
        }

        // import entity fields
        if ($existingEntity) {
            if ($isFullData) {
                $this->importExistingEntity($entity, $existingEntity, $itemData);
            }

            $entity = $existingEntity;
        }

        return $entity;
    }

    protected function getInventoryItemIdByProduct($product)
    {
        $identityValues = $this->fieldHelper->getIdentityValues($product);
        $productEntity  = $this->findEntityByIdentityValues(get_class($product), $identityValues);
        if (!$productEntity) {
            return false;
        }

        $inventoryItems = $productEntity->getInventoryItems();
        if ($inventoryItems->count() < 1) {
            return false;
        }

        // return first item from array since there can be only 1 inventory item for each product
        return $inventoryItems->first()->getId();
    }

    /**
     * @param object     $entity
     * @param array|null $itemData
     */
    protected function updateRelations($entity, array $itemData = null)
    {
        // update product
        $product = $entity->getProduct();
        if ($product) {
            $entity->setProduct($this->findExistingEntity($product));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function importExistingEntity(
        $entity,
        $existingEntity,
        $itemData = null,
        array $excludedFields = []
    ) {
        // manually handle recursive relation to accounts
        $entityName        = ClassUtils::getClass($entity);
        $fieldRelationName = 'product';
        $fieldName         = 'quantity';

        $fieldsExcluded = ($this->isFieldExcluded($entityName, $fieldRelationName, $itemData) ||
            $this->isFieldExcluded($entityName, $fieldName, $itemData));

        if ($entity instanceof InventoryItem
            && $existingEntity instanceof InventoryItem
            && !$fieldsExcluded
            && !array_intersect([$fieldRelationName, $fieldName], $excludedFields)
        ) {
            $oldQuantity = $existingEntity->getQuantity();
            $oldAllocatedQuantity = $existingEntity->getAllocatedQuantity();

            //manually handle quantity update
            $existingEntity->modifyQuantity($entity->getQuantity());

            $this->inventoryLogger->directLog(
                $existingEntity,
                'import',
                function (InventoryLog $log) use ($oldQuantity, $oldAllocatedQuantity) {
                    $log->setOldQuantity($oldQuantity);
                    $log->setOldAllocatedQuantity($oldAllocatedQuantity);
                }
            );

            //manually handle product relation
            $entity->setProduct($existingEntity->getProduct());

            $excludedFields = array_merge($excludedFields, [$fieldName, $fieldRelationName]);
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }
}
