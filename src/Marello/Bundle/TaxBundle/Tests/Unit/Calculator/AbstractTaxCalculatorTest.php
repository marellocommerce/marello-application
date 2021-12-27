<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Calculator;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;

use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;

abstract class AbstractTaxCalculatorTest extends TestCase
{
    /**
     * @var TaxCalculatorInterface
     */
    protected $calculator;

    /**
     * @var RoundingServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rounding;

    protected function setUp(): void
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
