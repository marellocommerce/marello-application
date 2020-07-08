<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Provider;

interface LastSyncProviderInterface
{
    /**
     * @param \DateTime $currentLastSyncItemDateTime
     * @param string $updatedAtColumnName
     * @param string $updatedAtColumnFormat
     * @param array $item
     * @param \DateTime|null $remoteServerDateTime
     *
     * @return \DateTime|null
     */
    public function getLastSyncItemDateTime(
        \DateTime $currentLastSyncItemDateTime,
        string $updatedAtColumnName,
        string $updatedAtColumnFormat,
        array $item,
        \DateTime $remoteServerDateTime = null
    ):?\DateTime;
}
