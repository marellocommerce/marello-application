<?php

namespace Marello\Bundle\TaxBundle\Resolver;

use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;

class RowTotalResolver
{
    /**
     * @var TaxCalculatorInterface
     */
    protected $calculator;

    /**
     * @param TaxCalculatorInterface   $calculator
     */
    public function __construct(TaxCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @param Result $result
     * @param float $price
     * @param int $quantity
     * @param TaxRule|null $taxRule
     */
    public function resolveRowTotal(Result $result, $price, $quantity = 1, TaxRule $taxRule = null)
    {
        $taxRate = null !== $taxRule && null !== $taxRule->getTaxRate() ? $taxRule->getTaxRate()->getRate() : 0.0;

        $taxableAmount = (float)$price * (float)$quantity;
        $resultElement = $this->getRowTotalResult($taxableAmount, $taxRate);

        $result->offsetSet(Result::ROW, $resultElement);
    }

    /**
     * @param float $taxableAmount
     * @param float $taxRate
     * @return ResultElement
     */
    protected function getRowTotalResult($taxableAmount, $taxRate)
    {
        $resultElement = $this->calculator->calculate($taxableAmount, $taxRate);

        return $resultElement;
    }
}
