<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use NumberFormatter as IntlNumberFormatter;
class OrderStatisticsCurrencyNumberFormatter
{
    /** @var NumberFormatter */
    protected $numberFormatter;

    /**
     * @param NumberFormatter $numberFormatter
     */
    public function __construct(NumberFormatter $numberFormatter)
    {
        $this->numberFormatter = $numberFormatter;
    }

    /**
     * @param float  $value
     * @param string $currencyCode
     * @param bool   $isDeviant
     *
     * @return string
     */
    public function formatValue($value, $currencyCode, $isDeviant = false)
    {
        $sign = null;

        if ($isDeviant && $value !== 0) {
            $sign  = $value > 0 ? '+' : '&minus;';
            $value = abs($value);
        }

        $value = $this->numberFormatter->formatCurrency($value, $currencyCode, [IntlNumberFormatter::MIN_FRACTION_DIGITS => 2]);

        return !is_null($sign) ? sprintf('%s%s', $sign, $value) : $value;
    }

    /**
     * Formats BigNumber result for view
     *
     * @param int    $value
     * @param string $dataType
     * @param array  $previousData
     *
     * @return array
     */
    public function formatResult(
        $value,
        $dataType,
        array $previousData = []
    ) {
        $result = ['value' => $this->formatValue($value, $dataType)];

        if (count($previousData)) {
            if (!$previousData['comparable']) {
                return $result;
            }

            $pastResult = $previousData['value'];
            $previousInterval = $previousData['dateRange'];
            $deviation = $value - $pastResult;
            $result['deviation'] = '';

            // Check that deviation won't be formatted as zero
            if (round($deviation, 2) != 0) {
                $result['deviation'] = $this->formatValue($deviation, $dataType, true);
                $result['isPositive'] = ($previousData['lessIsBetter'] xor ($deviation > 0));

                if ($pastResult != 0 && $dataType !== 'percent') {
                    $deviationPercent = $deviation / $pastResult;
                    $result['deviation'] .= sprintf(
                        ' (%s)',
                        $this->formatValue($deviationPercent, 'percent', true)
                    );
                }
            }

            $result['previousRange'] = $previousInterval;
        }

        return $result;
    }
}
