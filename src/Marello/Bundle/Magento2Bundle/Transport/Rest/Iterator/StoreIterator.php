<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\ImportExport\Converter\StoreDataConverter;
use Marello\Bundle\Magento2Bundle\Iterator\AbstractLoadeableIterator;
use Oro\Component\PhpUtils\ArrayUtil;

class StoreIterator extends AbstractLoadeableIterator
{
    private const ADMIN_STORE_ID = 0;

    /**
     * @var array
     */
    protected $storeData;

    /**
     * @var array
     */
    protected $storeConfigData;

    /**
     * @param array $storeData
     * @param array $storeConfigData
     */
    public function __construct(array $storeData, array $storeConfigData)
    {
        $this->storeData = $storeData;
        $this->storeConfigData = $storeConfigData;
    }

    /**
     * {@inheritDoc}
     */
    protected function getData(): array
    {
        $resultStoreData = [];
        foreach ($this->storeData as $storeDataItem) {
            if (!\array_key_exists(StoreDataConverter::ID_COLUMN_NAME, $storeDataItem)) {
                $resultStoreData[] = $storeDataItem;

                continue;
            }

            $currentStoreId = $storeDataItem[StoreDataConverter::ID_COLUMN_NAME];
            if (self::ADMIN_STORE_ID === $storeDataItem[StoreDataConverter::ID_COLUMN_NAME]) {
                continue;
            }

            $foundStoreConfigItem = ArrayUtil::find(function (array $storeConfigItem) use ($currentStoreId) {
                $storeId = $storeConfigItem[StoreDataConverter::ID_COLUMN_NAME] ?? null;

                return $currentStoreId === $storeId;
            }, $this->storeConfigData);

            if (null === $foundStoreConfigItem) {
                $resultStoreData[] = $storeDataItem;

                continue;
            }

            $resultStoreData[] = \array_merge($foundStoreConfigItem, $storeDataItem);
        }

        return $resultStoreData;
    }
}
