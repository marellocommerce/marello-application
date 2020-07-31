<?php

namespace Marello\Bundle\Magento2Bundle\Converter;

use Marello\Bundle\Magento2Bundle\DTO\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\StoreRepository;

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
        $storeOriginIds = [];

        $startDateTime = $connectorSearchSettingsDTO->getSyncStartDateTime(true);
        $endDateTime = (clone $startDateTime)->add(
            $connectorSearchSettingsDTO->getSyncDateInterval()
        );

        if ($connectorSearchSettingsDTO->getMissTimingDateInterval() instanceof \DateInterval) {
            $startDateTime->sub($connectorSearchSettingsDTO->getMissTimingDateInterval());
        }

        if (!$connectorSearchSettingsDTO->isNoWebsiteSet()) {
            $storeOriginIds = $this->storeRepository->getStoreOriginIdsByWebsiteId(
                $connectorSearchSettingsDTO->getWebsiteId()
            );
        }

        return new SearchParametersDTO(
            SearchParametersDTO::IMPORT_MODE_REGULAR,
            $startDateTime,
            $endDateTime,
            $storeOriginIds
        );
    }
}
