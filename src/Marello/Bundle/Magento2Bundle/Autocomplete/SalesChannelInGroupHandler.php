<?php

namespace Marello\Bundle\Magento2Bundle\Autocomplete;

use Marello\Bundle\Magento2Bundle\Exception\InvalidConfigurationException;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

class SalesChannelInGroupHandler extends SearchHandler
{
    private const DELIMITER = ';';

    /**
     * {@inheritdoc}
     */
    protected function checkAllDependenciesInjected()
    {
        if (!$this->entityRepository || !$this->idFieldName) {
            throw new InvalidConfigurationException('Search handler is not fully configured');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $parts = explode(self::DELIMITER, $search);
        if (3 !== count($parts)) {
            return [];
        }

        $searchTerm = $parts[0];
        $salesChannelGroupId = (int) $parts[1];
        $skippedSalesChannelIds = '' !== $parts[2] ? explode(',', $parts[2]) : [];

        $resultEntities = [];
        if (0 !== $salesChannelGroupId) {
            /** @var SalesChannelRepository $repository */
            $repository = $this->entityRepository;
            $queryBuilder = $repository->getActiveSalesChannelBySearchTermLimitedWithGroupIdQB(
                $searchTerm,
                $salesChannelGroupId,
                $skippedSalesChannelIds
            );

            $queryBuilder
                ->setFirstResult($firstResult)
                ->setMaxResults($maxResults);

            $resultEntities = $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
        }

        return $resultEntities;
    }
}
