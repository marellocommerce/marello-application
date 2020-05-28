<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

class SearchResponseDTO
{
    /** @var array */
    protected $items = [];

    /** @var int */
    protected $totalCount;

    /**
     * @param array $items
     * @param int $totalCount
     */
    public function __construct(array $items, int $totalCount)
    {
        $this->items = $items;
        $this->totalCount = $totalCount;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
