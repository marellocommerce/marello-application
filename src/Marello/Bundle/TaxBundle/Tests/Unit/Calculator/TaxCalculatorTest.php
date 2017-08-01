<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Calculator;

use Marello\Bundle\TaxBundle\Calculator\TaxCalculator;
use Marello\Bundle\TaxBundle\Model\TaxResult;

class TaxCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxCalculator
     */
    protected $taxCalculator;

    protected function setUp()
    {
        $this->taxCalculator = new TaxCalculator();
    }

    /**
     * @dataProvider calculateDataProvider
     *
     * @param float $amount
     * @param float $taxRate
     * @param array $result
     */
    public function testCalculate($amount, $taxRate, $result)
    {
        static::assertEquals(new TaxResult($result), $this->taxCalculator->calculate($amount, $taxRate));
    }

    /**
     * @return array
     */
    public function calculateDataProvider()
    {
        return [
            [
                'amount' => 150,
                'taxRate' => 0.1,
                'result' => [
                    TaxResult::INCLUDING_TAX => 150,
                    TaxResult::EXCLUDING_TAX => 135,
                    TaxResult::TAX_AMOUNT => 15
                ]
            ],
            [
                'amount' => 100,
                'taxRate' => 0.1,
                'result' => [
                    TaxResult::INCLUDING_TAX => 100,
                    TaxResult::EXCLUDING_TAX => 90,
                    TaxResult::TAX_AMOUNT => 10
                ]
            ]
        ];
    }
}
