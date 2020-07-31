<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

/**
 * Provides values to build search criteria, every connector should know how to use them
 */
class SearchParametersDTO
{
    public const IMPORT_MODE_INITIAL = 'initial';
    public const IMPORT_MODE_REGULAR  = 'regular';

    /** @var string */
    protected $mode = self::IMPORT_MODE_REGULAR;

    /**
     * Start date for read from
     *
     * @var \DateTime
     */
    protected $startDateTime;

    /**
     * End date for read to
     *
     * @var \DateTime
     */
    protected $endDateTime;

    /**
     * List of store ids that use to filter remote data,
     * in case of empty array store filters not applicable
     *
     * @var int[]
     */
    protected $storeOriginIds;

    /**
     * @param string $mode
     * @param \DateTime $startDateTime
     * @param \DateTime $endDateTime
     * @param array $storeOriginIds
     */
    public function __construct(
        string $mode,
        \DateTime $startDateTime,
        \DateTime $endDateTime,
        array $storeOriginIds = []
    ) {
        $this->mode = $mode;
        $this->startDateTime = clone $startDateTime;
        $this->endDateTime = clone $endDateTime;
        $this->storeOriginIds = $storeOriginIds;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return bool
     */
    public function isInitialMode(): bool
    {
        return $this->mode === self::IMPORT_MODE_INITIAL;
    }

    /**
     * @param bool $clone
     * @return \DateTime
     */
    public function getStartDateTime(bool $clone = true): \DateTime
    {
        if ($clone) {
            return clone $this->startDateTime;
        }

        return $this->startDateTime;
    }

    /**
     * @param bool $clone
     * @return \DateTime
     */
    public function getEndDateTime(bool $clone = true): \DateTime
    {
        if ($clone) {
            return clone $this->endDateTime;
        }

        return $this->endDateTime;
    }

    /**
     * @return int[]
     */
    public function getStoreOriginIds(): array
    {
        return $this->storeOriginIds;
    }
}
