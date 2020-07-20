<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SearchValueNormalizer implements NormalizerInterface
{
    public const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const ITERABLE_ELEMENT_DELIMITER = ',';

    /**
     * {@inheritDoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        if (null === $data) {
            return $data;
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if ($data instanceof \DateTime) {
            return $this->normalizeDateTimeData($data, $format, $context);
        }

        if (is_iterable($data)) {
            return $this->normalizeIterableData($data, $format, $context);
        }

        throw new InvalidArgumentException(
            sprintf('[Magento 2] An unexpected value could not be normalized: %s', var_export($data, true))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return null === $data || is_scalar($data) || $data instanceof \DateTime || \is_iterable($data);
    }

    /**
     * @param \DateTime $data
     * @param null $format
     * @param array $context
     * @return string
     */
    protected function normalizeDateTimeData(\DateTime $data, $format = null, array $context = []): string
    {
        if (null === $format) {
            $format = self::DEFAULT_DATETIME_FORMAT;
        }

        return $data->format($format);
    }

    /**
     * @param $data
     * @param null $format
     * @param array $context
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function normalizeIterableData($data, $format = null, array $context = []): string
    {
        $scalarItems = [];
        foreach ($data as $dataItem) {
            $scalarDataItem = $this->normalize($dataItem, $format, $context);
            if (null === $scalarDataItem) {
                continue;
            }

            $scalarItems[] = $scalarDataItem;
        }

        return implode(self::ITERABLE_ELEMENT_DELIMITER, $scalarItems);
    }
}
