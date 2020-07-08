<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector\Settings;

/**
 */
class ImportConnectorSearchSettingsDTO
{
    /** @var int */
    public const NO_WEBSITE_ID = 0;

    /** @var int */
    protected $websiteId = self::NO_WEBSITE_ID;

    /** @var \DateTime */
    protected $syncStartDate;

    /** @var \DateInterval */
    protected $syncRangeDateInterval;

    /** @var \DateInterval|null */
    protected $missTimingDateInterval = null;

    /**
     * @param \DateTime $syncStartDate
     * @param \DateInterval $syncRangeDateInterval
     * @param int $websiteId
     * @param \DateInterval|null $missTimingDateInterval
     */
    public function __construct(
        \DateTime $syncStartDate,
        \DateInterval $syncRangeDateInterval,
        int $websiteId = self::NO_WEBSITE_ID,
        \DateInterval $missTimingDateInterval = null
    ) {
        $this->syncStartDate = $syncStartDate;
        $this->syncRangeDateInterval = $syncRangeDateInterval;
        $this->websiteId = $websiteId;
        $this->missTimingDateInterval = $missTimingDateInterval;
    }

    /**
     * @return \DateTime
     */
    public function getSyncStartDate(): \DateTime
    {
        return $this->syncStartDate;
    }

    /**
     * @return \DateInterval
     */
    public function getSyncRangeDateInterval(): \DateInterval
    {
        return $this->syncRangeDateInterval;
    }

    /**
     * @return int
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * @return \DateInterval|null
     */
    public function getMissTimingDateInterval(): ?\DateInterval
    {
        return $this->missTimingDateInterval;
    }

    /**
     * @return bool
     */
    public function isNoWebsiteSet(): bool
    {
        return $this->websiteId === self::NO_WEBSITE_ID;
    }
}
