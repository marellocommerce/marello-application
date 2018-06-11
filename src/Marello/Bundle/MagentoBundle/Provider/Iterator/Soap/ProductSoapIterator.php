<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use Marello\Bundle\MagentoBundle\Entity\Website;
use Marello\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Marello\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class ProductSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * @param \DateTime $date
     * @param array $websiteIds
     * @param array $storeIds
     * @param string $format
     *
     * @return array
     */
    protected function getBatchFilter(
        \DateTime $date,
        array $websiteIds = [],
        array $storeIds = [],
        $format = 'Y-m-d H:i:s'
    ) {
        if ($this->isInitialSync()) {
            $this->filter->addDateFilter('created_at', 'from', $this->getToDateInitial($date), $format);
            $this->filter->addDateFilter('created_at', 'to', $date, $format);
        } else {
            $this->filter->addDateFilter('updated_at', 'from', $date);
            $this->filter->addDateFilter('updated_at', 'to', $date->add($this->syncRange));
        }

        $this->modifyFilters();
        $this->logAppliedFilters($this->filter);

        return $this->filter->getAppliedFilters();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityIds()
    {
        $filters = $this->getBatchFilter($this->lastSyncDate);

        $this->loadByFilters($filters);

        return array_keys($this->entityBuffer);
    }

    /**
     * @param array $ids
     */
    protected function loadEntities(array $ids)
    {
        if (!$ids) {
            return;
        }

        $filters = new BatchFilterBag();
        $filters->addComplexFilter(
            'in',
            [
                'key' => $this->getIdFieldName(),
                'value' => [
                    'key' => 'in',
                    'value' => implode(',', $ids)
                ]
            ]
        );

        if (null !== $this->websiteId && $this->websiteId !== Website::ALL_WEBSITES) {
            $filters->addWebsiteFilter([$this->websiteId]);
        }

        $this->loadByFilters($filters->getAppliedFilters());
    }

    /**
     * @param array $filters
     */
    protected function loadByFilters(array $filters)
    {
        $result = $this->transport->call(SoapTransport::ACTION_PRODUCT_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $ids = array_map(
            function ($item) {
                return is_object($item) ? $item->product_id : $item['product_id'];
            },
            $result
        );

        $this->entityBuffer = array_combine($ids, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        if (!array_key_exists($id, $this->entityBuffer)) {
            $this->logger->warning(sprintf('Entity with id "%s" was not found', $id));

            return false;
        }

        $result = $this->entityBuffer[$id];

        return ConverterUtils::objectToArray($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'product_id';
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading Product by id: %s', $this->key()));

        return $this->current;
    }
}
