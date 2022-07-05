<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

abstract class AbstractInventoryLevelReader extends AbstractReader
{
    const SKU = 'SKU';
    const WAREHOUSE_CODE = 'Warehouse Code';
    const ADJUSTMENT = 'Adjustment';
    const BATCH_NUMBER = 'Batch Number';
    const PURCHASE_PRICE = 'Purchase Price';
    const EXPIRATION_DATE = 'Expiration Date';

    protected $readIndex = 0;
    protected $items = [];

    /**
     * @return InventoryLevel[]
     */
    abstract protected function getInventoryLevels();
    
    /**
     * @return array
     */
    protected function getItems()
    {
        if (empty($this->items)) {
            $invLevels = $this->getInventoryLevels();
            if (!empty($invLevels)) {
                foreach ($invLevels as $invLevel) {
                    $invItem = $invLevel->getInventoryItem();
                    $warehouse = $invLevel->getWarehouse();
                    $invBatches = $invLevel->getInventoryBatches();
                    if ($invItem->isEnableBatchInventory() && $invBatches->count() > 0) {
                        foreach ($invBatches as $batch) {
                            $this->items[] = [
                                self::SKU => $invItem->getProduct()->getSku(),
                                self::WAREHOUSE_CODE => $warehouse->getCode(),
                                self::ADJUSTMENT => $batch->getQuantity(),
                                self::BATCH_NUMBER => $batch->getBatchNumber(),
                                self::PURCHASE_PRICE => $batch->getPurchasePrice(),
                                self::EXPIRATION_DATE => $batch->getExpirationDate()
                            ];
                        }
                    } else {
                        $this->items[] = [
                            self::SKU => $invItem->getProduct()->getSku(),
                            self::WAREHOUSE_CODE => $warehouse->getCode(),
                            self::ADJUSTMENT => $invLevel->getInventoryQty(),
                            self::BATCH_NUMBER => null,
                            self::PURCHASE_PRICE => null,
                            self::EXPIRATION_DATE => null
                        ];
                    }
                }
            }
        }

        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $item = null;
        $items = $this->getItems();
        if ($this->readIndex < count($items)) {
            $item = $items[$this->readIndex];
            $this->readIndex++;
            $context = $this->getContext();
            $context->incrementReadOffset();
            $context->incrementReadCount();
        }

        return $item;
    }
}
