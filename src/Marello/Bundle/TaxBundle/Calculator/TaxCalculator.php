<?php

namespace Marello\Bundle\TaxBundle\Calculator;

use Marello\Bundle\TaxBundle\Model\ResultElement;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;

class TaxCalculator implements TaxCalculatorInterface
{
    /**
     * @var RoundingServiceInterface
     */
    protected $rounding;

    /**
     * @param RoundingServiceInterface $rounding
     */
    public function __construct(RoundingServiceInterface $rounding)
    {
        $this->rounding = $rounding;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($amount, $taxRate)
    {
        $inclTax = (double)$amount;
        $taxRate = (float) $taxRate;

        $taxAmount = $inclTax * $taxRate;
        $exclTax = $inclTax - $taxAmount;

        return ResultElement::create(
            $this->rounding->round($inclTax),
            $this->rounding->round($exclTax),
            $this->rounding->round($taxAmount)
        );
    }
}
