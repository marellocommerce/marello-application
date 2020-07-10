<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

use Marello\Bundle\Magento2Bundle\Exception\InvalidArgumentException;

class SortOrder
{
    public const DIRECTION_ASC = 'ASC';
    public const DIRECTION_DESC = 'DESC';

    /** @var string */
    protected $direction;

    /** @var string */
    protected $fieldName;

    /**
     * @param string $fieldName
     * @param string $direction
     */
    public function __construct(string $fieldName, string $direction = self::DIRECTION_DESC)
    {
        if (!\in_array($direction, [static::DIRECTION_ASC, static::DIRECTION_DESC], true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Given not supported direction "%s", please use one of this instead "%s".',
                    $direction,
                    static::DIRECTION_ASC . ', ' . static::DIRECTION_DESC
                )
            );
        }

        $this->fieldName = $fieldName;
        $this->direction = $direction;
    }

    /**
     * @return array
     */
    public function getSortOrderParams(): array
    {
        return [
            'field' => $this->fieldName,
            'direction' => $this->direction
        ];
    }
}
