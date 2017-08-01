<?php

namespace Marello\Bundle\TaxBundle\Calculator;

use Marello\Bundle\TaxBundle\Model\TaxResult;

interface TaxCalculatorInterface
{
    /**
     * @param string $amount
     * @param string $taxRate
     * @return TaxResult
     *      includingTax - amount with tax
     *      excludingTax - amount without tax
     *      taxAmount    - tax amount
     */
    public function calculate($amount, $taxRate);
}
