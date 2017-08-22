<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Calculator;

use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;

abstract class AbstractTaxCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxCalculatorInterface
     */
    protected $calculator;

    /**
     * @var RoundingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rounding;

    protected function setUp()
    {
        $this->rounding = $this
            ->getMockBuilder(RoundingServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->calculator = $this->getCalculator();
    }

    /**
     * @param array $expectedResult
     * @param string $taxableAmount
     * @param string $taxRate
     *
     * @dataProvider calculateDataProvider
     */
    public function testCalculate($expectedResult, $taxableAmount, $taxRate)
    {
        $this->rounding
            ->expects(static::any())
            ->method('round')
            ->willReturnCallback(
                function ($value) {
                    return round($value, 2);
                }
            );
        $this->assertEquals(
            $expectedResult,
            array_values($this->calculator->calculate($taxableAmount, $taxRate)->getArrayCopy())
        );
    }

    /**
     * @return TaxCalculatorInterface
     */
    abstract protected function getCalculator();
}
