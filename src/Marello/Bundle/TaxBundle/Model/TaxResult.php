<?php

namespace Marello\Bundle\TaxBundle\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class TaxResult extends ParameterBag
{
    const INCLUDING_TAX = 'includingTax';
    const EXCLUDING_TAX = 'excludingTax';
    const TAX_AMOUNT = 'taxAmount';

    /**
     * @return string
     */
    public function getIncludingTax()
    {
        return $this->get(self::INCLUDING_TAX);
    }

    /**
     * @return string
     */
    public function getExcludingTax()
    {
        return $this->get(self::EXCLUDING_TAX);
    }

    /**
     * @return string
     */
    public function getTaxAmount()
    {
        return $this->get(self::TAX_AMOUNT);
    }
}
