<?php

namespace Marello\Bundle\CatalogBundle\Formatter;

class CategoryCodeFormatter
{
    /**
     * @param string $inputValue
     * @return string
     */
    public function format($inputValue)
    {
        return trim(preg_replace('/_+/', '_', preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($inputValue))), '_');
    }
}
