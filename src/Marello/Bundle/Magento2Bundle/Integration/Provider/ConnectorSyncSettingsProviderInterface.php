<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Provider;

use Marello\Bundle\Magento2Bundle\DTO\ConnectorSyncSettingsDTO;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

interface ConnectorSyncSettingsProviderInterface
{
    /**
     * @param Integration $integration
     * @param ConnectorInterface[] $connectors
     * @param bool $forceSync
     *
     * @return ConnectorSyncSettingsDTO[]
     */
    public function getConnectorSyncSettingDTOs(
        Integration $integration,
        array $connectors,
        bool $forceSync = false
    ): array;
}
