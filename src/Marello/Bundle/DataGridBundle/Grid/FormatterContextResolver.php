<?php
/**
 * This application uses Open Source components. You can find the source code
 * of their open source projects along with license information below. We acknowledge
 * and are grateful to these developers for their contributions to open source.
 *
 * Project: OroCRM (https://github.com/orocrm)
 * Copyright 2013 Oro Inc. All right reserved.
 * License (OSL-3.0) (https://github.com/orocrm/crm-application/blob/master/LICENSE)
 */
namespace Marello\Bundle\DataGridBundle\Grid;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;

class FormatterContextResolver
{
    /**
     * Return currency from given row
     *
     * @return callable
     */
    public static function getResolverCurrencyClosure()
    {
        return function (ResultRecordInterface $record, $value, NumberFormatter $formatter) {
            return [$record->getValue('currency')];
        };
    }

    /**
     * Return currency from given row
     *
     * @return callable
     */
    public static function getRootResolverCurrencyClosure()
    {
        return function (ResultRecordInterface $record, $value, NumberFormatter $formatter) {
            if ($record->getRootEntity() instanceof CurrencyAwareInterface) {
                return [$record->getRootEntity()->getCurrency()];
            }

            throw new \LogicException(
                sprintf(
                    '%s does not implement %s',
                    get_class($record->getRootEntity()),
                    CurrencyAwareInterface::class
                )
            );
        };
    }
}
