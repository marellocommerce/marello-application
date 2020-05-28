<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

class Filter
{
    public const CONDITION_EQ = 'eq';
    public const CONDITION_FINSET = 'finset';
    public const CONDITION_FROM = 'from';
    public const CONDITION_GT = 'gt';
    public const CONDITION_GTEQ = 'gteq';
    public const CONDITION_IN = 'in';
    public const CONDITION_LIKE = 'like';
    public const CONDITION_LT = 'lt';
    public const CONDITION_LTEQ = 'lteq';
    public const CONDITION_MOREQ = 'moreq';
    public const CONDITION_NEQ = 'neq';
    public const CONDITION_NFINSET = 'nfinset';
    public const CONDITION_NIN = 'nin';
    public const CONDITION_NOTNULL = 'notnull';
    public const CONDITION_NULL = 'null';
    public const CONDITION_TO = 'to';

    /** @var string[] */
    protected $allowedConditions = [
        self::CONDITION_EQ,
        self::CONDITION_FINSET,
        self::CONDITION_FROM,
        self::CONDITION_GT,
        self::CONDITION_GTEQ,
        self::CONDITION_IN,
        self::CONDITION_LIKE,
        self::CONDITION_LT,
        self::CONDITION_LTEQ,
        self::CONDITION_MOREQ,
        self::CONDITION_NEQ,
        self::CONDITION_NFINSET,
        self::CONDITION_NIN,
        self::CONDITION_NOTNULL,
        self::CONDITION_NULL,
        self::CONDITION_TO
    ];

    /** @var string */
    protected $conditionType;

    /** @var string */
    protected $fieldName;

    /** @var string */
    protected $searchValue;

    /**
     * @param string $fieldName
     * @param string $conditionType
     * @param string|null $searchValue
     */
    public function __construct(
        string $fieldName,
        string $conditionType = self::CONDITION_EQ,
        string $searchValue = null
    ) {
        if (!\in_array($conditionType, $this->allowedConditions, true)) {
            throw new \LogicException(
                sprintf(
                    'Given not supported filter condition type "%s" , please use one of this instead "%s".',
                    $this->conditionType,
                    implode(', ', $this->allowedConditions)
                )
            );
        }

        $this->fieldName = $fieldName;
        $this->conditionType = $conditionType;
        $this->searchValue = $searchValue;
    }

    /**
     * @return array
     */
    public function getFilterParams(): array
    {
        return [
            'field' => $this->fieldName,
            'value' => $this->searchValue,
            'condition_type' => $this->conditionType
        ];
    }
}
