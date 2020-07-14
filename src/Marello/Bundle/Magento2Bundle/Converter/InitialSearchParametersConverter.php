<?php

namespace Marello\Bundle\Magento2Bundle\Converter;

use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\StoreRepository;
use Marello\Bundle\Magento2Bundle\Integration\Connector\Settings\ImportConnectorSearchSettingsDTO;

class InitialSearchParametersConverter implements SearchParametersConverterInterface
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

        /**
         * Initial sync goes in reverse direction,
         * from point when integration was saved to syncStartDate
         */
        $endDateTime = clone $connectorSearchSettingsDTO->getSyncStartDate();
        if ($connectorSearchSettingsDTO->getMissTimingDateInterval() instanceof \DateInterval) {
            $endDateTime->add($connectorSearchSettingsDTO->getMissTimingDateInterval());
        }

        $startDateTime = (clone $endDateTime)->sub(
            $connectorSearchSettingsDTO->getSyncRangeDateInterval()
        );

        if (!$connectorSearchSettingsDTO->isNoWebsiteSet()) {
            $originStoreIds = $this->storeRepository->getOriginStoreIdsByWebsiteId(
                $connectorSearchSettingsDTO->getWebsiteId()
            );
        }

        return new SearchParametersDTO(
            SearchParametersDTO::IMPORT_MODE_INITIAL,
            $startDateTime,
            $endDateTime,
            $originStoreIds
        );
    }
}
