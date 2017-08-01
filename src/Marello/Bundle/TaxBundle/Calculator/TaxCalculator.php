<?php

namespace Marello\Bundle\TaxBundle\Calculator;

use Marello\Bundle\TaxBundle\Model\TaxResult;

class TaxCalculator implements TaxCalculatorInterface
{
    /** {@inheritdoc} */
    public function calculate($amount, $taxRate)
    {
        $inclTax = (double)$amount;
        $taxRate = (float) $taxRate;

        $taxAmount = $inclTax * $taxRate;
        $exclTax = $inclTax - $taxAmount;

        return new TaxResult(
            [
                TaxResult::INCLUDING_TAX => $inclTax,
                TaxResult::EXCLUDING_TAX => $exclTax,
                TaxResult::TAX_AMOUNT => $taxAmount
            ]
        );
    }
}
