<?php

namespace Marello\Bundle\SalesBundle\Form\Converter;

use Oro\Bundle\FormBundle\Autocomplete\ConverterInterface;

class SalesChannelTypeConverter implements ConverterInterface
{
    public function convertItem($item)
    {
        return [
            'id' => $item->getName(),
            'label' => $item->getLabel(),
        ];
    }
}
