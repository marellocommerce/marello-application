<?php

namespace Marello\Bundle\Magento2Bundle\Converter;

use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\StoreRepository;
use Marello\Bundle\Magento2Bundle\Integration\Connector\Settings\ImportConnectorSearchSettingsDTO;

class GeneralSearchParametersConverter implements SearchParametersConverterInterface
{
    /** @var StoreRepository */
    protected $storeRepository;

    /**
     * @param StoreRepository $storeRepository
     */
    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function convertConnectorSearchSettings(ImportConnectorSearchSettingsDTO $connectorSearchSettingsDTO): SearchParametersDTO
    {
        $originStoreIds = [];

        $startDateTime = clone $connectorSearchSettingsDTO->getSyncStartDate();
        if ($connectorSearchSettingsDTO->getMissTimingDateInterval() instanceof \DateInterval) {
            $startDateTime->sub($connectorSearchSettingsDTO->getMissTimingDateInterval());
        }

        $endDateTime = (clone $startDateTime)->add(
            $connectorSearchSettingsDTO->getSyncRangeDateInterval()
        );

        if (!$connectorSearchSettingsDTO->isNoWebsiteSet()) {
            $originStoreIds = $this->storeRepository->getOriginStoreIdsByWebsiteId(
                $connectorSearchSettingsDTO->getWebsiteId()
            );
        }

        return new SearchParametersDTO(
            SearchParametersDTO::IMPORT_MODE_REGULAR,
            $startDateTime,
            $endDateTime,
            $originStoreIds
        );
    }
}
