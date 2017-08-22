<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Resolver;

use Marello\Bundle\TaxBundle\Calculator\TaxCalculatorInterface;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Resolver\RowTotalResolver;

class RowTotalResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RowTotalResolver
     */
    protected $resolver;

    /**
     * @var TaxCalculatorInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculator;

    protected function setUp()
    {
        $this->calculator = $this->getMockBuilder(TaxCalculatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new RowTotalResolver($this->calculator);
    }

    protected function tearDown()
    {
        unset($this->calculator, $this->resolver, $this->settingsProvider);
    }

    public function testEmptyTaxRule()
    {
        $result = new Result();
        $amount = 0.0;
        $taxRate = 0.0;
        $resultElement = new ResultElement();

        $this->calculator->expects($this->once())
            ->method('calculate')
            ->with($amount, $taxRate)
            ->willReturn($resultElement);

        $this->resolver->resolveRowTotal($result, $amount, 0);

        $this->assertEquals($resultElement, $result->getRow());
    }

    /**
     * @dataProvider rowTotalDataProvider
     * @param float      $amount
     * @param TaxRule    $taxRule
     * @param array      $expected
     * @param string     $taxRate
     * @param int        $quantity
     */
    public function testResolveRowTotal(
        $amount,
        $taxRule,
        array $expected,
        $taxRate,
        $quantity
    ) {
        $result = new Result();

        $calculateAmount = (float)$amount * (float)$quantity;

        $this->calculator->expects($this->once())
            ->method('calculate')
            ->with($calculateAmount, $taxRate)
            ->willReturn($expected['row']);

        $this->resolver->resolveRowTotal($result, $amount, $quantity, $taxRule);
        $this->assertEquals($expected['row'], $result->getRow());
    }

    /**
     * @return array
     */
    public function rowTotalDataProvider()
    {
        return [
            [
                19.99,
                $this->getTaxRule('city', '0.08'),
                [
                    'row' => ResultElement::create('0.01255', '0.02365', '0.035655'),

                ],
                '0.08',
                1
            ],
            [
                19.99,
                $this->getTaxRule('region', '0.07'),
                [
                    'row' => ResultElement::create('0.01255', '0.02365', '0.035655'),
                ],
                '0.07',
                2,
                true
            ]
        ];
    }

    /**
     * @param string $taxCode
     * @param string $rate
     * @return TaxRule
     */
    protected function getTaxRule($taxCode, $rate)
    {
        $taxRule = new TaxRule();
        $taxRate = new TaxRate();
        $taxRate
            ->setRate($rate)
            ->setCode($taxCode);
        $taxRule->setTaxRate($taxRate);
        return $taxRule;
    }
}
