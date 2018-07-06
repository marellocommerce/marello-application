<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Marello\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

/**
 * @deprecated entire class to be removed
 * Class RegionBridgeIterator
 * @package Marello\Bundle\MagentoBundle\Provider\Iterator\Soap
 */
class RegionBridgeIterator extends AbstractBridgeIterator
{
    /**
     * Those are very small objects (4 properties), so we can afford for bigger page size
     */
    const DEFAULT_REGION_PAGE_SIZE = 500;

    /**
     * {@inheritdoc}
     */
    public function __construct(MagentoTransportInterface $transport, array $settings)
    {
        parent::__construct($transport, $settings);

        $this->pageSize = !empty($settings['page_size']) ? (int)$settings['page_size'] : self::DEFAULT_REGION_PAGE_SIZE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $filters = $this->filter->getAppliedFilters();
        $filters['pager'] = ['page' => $this->getCurrentPage(), 'pageSize' => $this->pageSize];

        $this->loadByFilters($filters);

        return array_keys($this->entityBuffer);
    }

    /**
     * @param array $filters
     */
    protected function loadByFilters(array $filters)
    {
        $result = $this->transport->call(SoapTransport::ACTION_ORO_REGION_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $this->entityBuffer = [];
        foreach ($result as $obj) {
            $this->entityBuffer[$obj->region_id] = (array)$obj;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'region_id';
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading Region by id: %s', $this->key()));

        return $this->current;
    }
}
