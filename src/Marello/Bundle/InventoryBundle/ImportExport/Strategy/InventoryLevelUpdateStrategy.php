<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryLevelUpdateStrategy extends ConfigurableAddOrReplaceStrategy
{
    /** @var InventoryLevelCalculator $levelCalculator */
    protected $levelCalculator;

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
        $inventoryLevel = $this->findInventoryLevel($entity);

        if (!$inventoryLevel) {
            return null;
        }

        $operator = $this->getAdjustmentOperator($entity->getInventoryQty());
        $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
            $inventoryLevel,
            $inventoryLevel->getInventoryItem(),
            $this->levelCalculator->calculateAdjustment($operator, $entity->getInventoryQty()),
            0,
            'import'
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );

        // deliberately return a different entity than the initial imported entity during errors with multiple
        $product = $this->databaseHelper->findOneBy(Product::class, ['sku' => $entity->getInventoryItem()->getProduct()->getSku()]);
        return $this->databaseHelper->findOneBy(InventoryItem::class, ['product' => $product->getId()]);
    }

    /**
     * Get adjustment operator
     * @param $inventoryQty
     * @return string
     */
    protected function getAdjustmentOperator($inventoryQty)
    {
        if ($inventoryQty < 0) {
            return InventoryLevelCalculator::OPERATOR_DECREASE;
        }

        return InventoryLevelCalculator::OPERATOR_INCREASE;
    }

    /**
     * @param InventoryLevel $entity
     *
     * @return null|object|InventoryLevel
     */
    protected function findInventoryLevel($entity)
    {
        $product = $this->databaseHelper->findOneBy(Product::class, ['sku' => $entity->getInventoryItem()->getProduct()->getSku()]);
        $inventoryItem = $this->databaseHelper->findOneBy(InventoryItem::class, ['product' => $product->getId()]);

        if (!$inventoryItem) {
            return null;
        }

        $level = $this->databaseHelper->findOneBy(InventoryLevel::class, [
            'inventoryItem' => $inventoryItem->getId(),
//            'warehouse'     => $entity->getWarehouse()->getId()
        ]);

        return $level;
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

    /**
     * Set inventoryLevel calculator
     * @param InventoryLevelCalculator $levelCalculator
     */
    public function setLevelCalculator(InventoryLevelCalculator $levelCalculator)
    {
        $this->levelCalculator = $levelCalculator;
    }
}
