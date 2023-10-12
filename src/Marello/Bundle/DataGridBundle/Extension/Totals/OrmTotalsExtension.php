<?php

namespace Marello\Bundle\DataGridBundle\Extension\Totals;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\DataGridBundle\Extension\Totals\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Totals\OrmTotalsExtension as BaseOrmTotalsExtension;

class OrmTotalsExtension extends BaseOrmTotalsExtension
{
    /**
     * {@inheritDoc}
     */
    protected function getTotalData($rowConfig, $data)
    {
        if (empty($data)) {
            return [];
        }

        $columns = [];
        foreach ($rowConfig['columns'] as $field => $total) {
            $column = [];
            if (isset($data[$field])) {
                $totalValue = $data[$field];
                if (isset($total[Configuration::TOTALS_DIVISOR_KEY])) {
                    $divisor = (int) $total[Configuration::TOTALS_DIVISOR_KEY];
                    if ($divisor != 0) {
                        $totalValue = $totalValue / $divisor;
                    }
                }
                if (isset($total[Configuration::TOTALS_FORMATTER_KEY])) {
                    if ($total[Configuration::TOTALS_FORMATTER_KEY] === 'currency' && isset($data['currency'])) {
                        $totalValue = $this->applyFrontendFormatting(
                            [$totalValue, $data['currency']],
                            $total[Configuration::TOTALS_FORMATTER_KEY]
                        );
                    } else {
                        $totalValue = $this->applyFrontendFormatting(
                            $totalValue,
                            $total[Configuration::TOTALS_FORMATTER_KEY]
                        );
                    }
                }
                $column['total'] = $totalValue;
            }
            if (isset($total[Configuration::TOTALS_LABEL_KEY])) {
                $column[Configuration::TOTALS_LABEL_KEY] =
                    $this->translator->trans($total[Configuration::TOTALS_LABEL_KEY]);
            }
            $columns[$field] = $column;
        };

        return ['columns' => $columns];
    }

    /**
     * {@inheritDoc}
     */
    protected function applyFrontendFormatting($val = null, $formatter = null)
    {
        if (null === $formatter) {
            return $val;
        }

        switch ($formatter) {
            case PropertyInterface::TYPE_DATE:
                $val = $this->dateTimeFormatter->formatDate($val);
                break;
            case PropertyInterface::TYPE_DATETIME:
                $val = $this->dateTimeFormatter->format($val);
                break;
            case PropertyInterface::TYPE_TIME:
                $val = $this->dateTimeFormatter->formatTime($val);
                break;
            case PropertyInterface::TYPE_DECIMAL:
            case PropertyInterface::TYPE_INTEGER:
                $val = $this->numberFormatter->formatDecimal($val);
                break;
            case PropertyInterface::TYPE_PERCENT:
                $val = $this->numberFormatter->formatPercent($val);
                break;
            case PropertyInterface::TYPE_CURRENCY:
                $val = is_array($val) ?
                    $this->numberFormatter->formatCurrency($val[0], $val[1]) :
                    $this->numberFormatter->formatCurrency($val);
                break;
        }

        return $val;
    }
}
