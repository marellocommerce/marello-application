<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

class ConnectorSyncSettingsDTO
{
    /** @var ConnectorInterface */
    protected $connector;

    /** @var \DateTime */
    protected $syncStartDateTime;

    /** @var \DateInterval */
    protected $syncDateInterval;

    /** @var \DateInterval */
    protected $missTimingDateInterval;

    /** @var SalesChannelInfo|null */
    protected $salesChannelInfo;

    /**
     * @param ConnectorInterface $connector
     * @param \DateTime $syncStartDateTime
     * @param \DateInterval $syncDateInterval
     * @param \DateInterval $missTimingDateInterval
     * @param SalesChannelInfo|null $salesChannelInfo
     */
    public function __construct(
        ConnectorInterface $connector,
        \DateTime $syncStartDateTime,
        \DateInterval $syncDateInterval,
        \DateInterval $missTimingDateInterval,
        SalesChannelInfo $salesChannelInfo = null
    ) {
        $this->connector = $connector;
        $this->syncStartDateTime = clone $syncStartDateTime;
        $this->syncDateInterval = clone $syncDateInterval;
        $this->missTimingDateInterval = clone $missTimingDateInterval;
        $this->salesChannelInfo = $salesChannelInfo;
    }

    /**
     * @return ConnectorInterface
     */
    public function getConnector(): ConnectorInterface
    {
        return $this->connector;
    }

    /**
     * @param bool $clone
     * @return \DateTime
     */
    public function getSyncStartDateTime(bool $clone = true): \DateTime
    {
        if ($clone) {
            return clone $this->syncStartDateTime;
        }

        return $this->syncStartDateTime;
    }

    /**
     * @return \DateInterval
     */
    public function getSyncDateInterval(): \DateInterval
    {
        return $this->syncDateInterval;
    }

    /**
     * @return \DateInterval
     */
    public function getMissTimingDateInterval(): \DateInterval
    {
        return $this->missTimingDateInterval;
    }

    /**
     * @return int|null
     */
    public function getWebsiteId(): ?int
    {
        return $this->salesChannelInfo ? $this->salesChannelInfo->getWebsiteId() : null;
    }

    /**
     * @return int|null
     */
    public function getSalesChannelId(): ?int
    {
        return $this->salesChannelInfo ? $this->salesChannelInfo->getSalesChannelId() : null;
    }

    /**
     * @param \DateTime $startDate
     * @return $this
     */
    public function createSettingsWithNewStartDate(\DateTime $startDate): self
    {
        return new static(
            $this->connector,
            $startDate,
            $this->syncDateInterval,
            $this->missTimingDateInterval,
            $this->salesChannelInfo
        );
    }
}
