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
    protected $originStoreIds;

    /**
     * @param string $mode
     * @param \DateTime $startDateTime
     * @param \DateTime $endDateTime
     * @param array $originStoreIds
     */
    public function __construct(
        string $mode,
        \DateTime $startDateTime,
        \DateTime $endDateTime,
        array $originStoreIds = []
    ) {
        $this->mode = $mode;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->originStoreIds = $originStoreIds;
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
     * @return \DateTime
     */
    public function getStartDateTime(): \DateTime
    {
        return $this->startDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndDateTime(): \DateTime
    {
        return $this->endDateTime;
    }

    /**
     * @return int[]
     */
    public function getOriginStoreIds(): array
    {
        return $this->originStoreIds;
    }
}
