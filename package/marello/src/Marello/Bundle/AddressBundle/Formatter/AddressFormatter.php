<?php

namespace Marello\Bundle\AddressBundle\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter as BaseAddressFormatter;

class AddressFormatter extends BaseAddressFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function getValue($obj, $property)
    {
        if ($property === 'phone') {
            return null;
        }
        
        return parent::getValue($obj, $property);
    }
}
