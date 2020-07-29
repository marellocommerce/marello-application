<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\Persistence\ObjectRepository;

interface NotInOriginIdsInterface extends ObjectRepository
{
    /**
     * @param array $existedRecordsOriginIds
     * @param int $integrationId
     * @return object[]
     */
    public function getEntitiesNotInOriginIdsInGivenIntegration(
        array $existedRecordsOriginIds,
        int $integrationId
    ): array;
}
