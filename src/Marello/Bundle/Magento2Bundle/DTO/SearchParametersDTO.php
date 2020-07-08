<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

/**
 * Provides values for parameter that uses to make correct search request
 *
 * @todo
 * Add sort order and sort order params
 * Add pageCount
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
     * @var string
     */
    protected $dateFieldName;

    /**
     * @var bool
     */
    protected $useOrderDESC;

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
     * @param string $dateFieldName
     * @param bool $useOrderDESC
     * @param array $originStoreIds
     */
    public function __construct(
        string $mode,
        \DateTime $startDateTime,
        \DateTime $endDateTime,
        string $dateFieldName,
        bool $useOrderDESC = false,
        array $originStoreIds = []
    ) {
        $this->mode = $mode;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->dateFieldName = $dateFieldName;
        $this->useOrderDESC = $useOrderDESC;
        $this->originStoreIds = $originStoreIds;
    }

//    /**
//     * @return mixed
//     */
//    public function getPageSize()
//    {
//        return $this->pageSize;
//    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
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
     * @return string
     */
    public function getDateFieldName(): string
    {
        return $this->dateFieldName;
    }

    public function isOrderDESC(): bool
    {
        return $this->useOrderDESC;
    }

    /**
     * @return int[]
     */
    public function getOriginStoreIds(): array
    {
        return $this->originStoreIds;
    }
}
