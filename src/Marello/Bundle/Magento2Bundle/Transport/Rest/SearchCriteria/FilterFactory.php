<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FilterFactory implements FilterFactoryInterface
{
    /**
     * @var NormalizerInterface
     */
    protected $searchValueNormalizer;

    /**
     * @param NormalizerInterface $searchValueNormalizer
     */
    public function __construct(NormalizerInterface $searchValueNormalizer)
    {
        $this->searchValueNormalizer = $searchValueNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public function createFilter(
        string $fieldName,
        string $conditionType,
        $searchValue,
        string $searchValueFormat = null,
        array $searchValueContext = []
    ): Filter {
        if (!$this->searchValueNormalizer->supportsNormalization($searchValue)) {
            throw new RuntimeException('Normalization of search filter value is not supported.');
        }

        $normalizedSearchValue = $this->searchValueNormalizer->normalize(
            $searchValue,
            $searchValueFormat,
            $searchValueContext
        );

        return new Filter($fieldName, $conditionType, $normalizedSearchValue);
    }
}
