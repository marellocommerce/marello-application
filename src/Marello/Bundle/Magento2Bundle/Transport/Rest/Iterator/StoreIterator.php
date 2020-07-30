<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\Iterator\AbstractLoadeableIterator;

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
        $data = [];
        $storeIds = \array_unique(
            \array_column($this->storeData, 'id')
        );

        $storeIdsConfig = \array_column($this->storeConfigData, 'id');
        foreach ($storeIds as $storeItemIndex => $storeId) {
            $configItemIndex = \array_search($storeId, $storeIdsConfig, true);

            if (self::ADMIN_STORE_ID === $storeId) {
                continue;
            }

            $storeDataItem = $this->storeData[$storeItemIndex];
            $storeConfigDataItem = [];
            if (false !== $configItemIndex) {
                $storeConfigDataItem = $this->storeConfigData[$configItemIndex];
            }

            $data[] = \array_merge($storeConfigDataItem, $storeDataItem);
        }

        return $data;
    }
}
