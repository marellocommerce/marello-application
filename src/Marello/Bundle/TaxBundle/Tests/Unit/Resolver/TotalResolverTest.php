<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Resolver;

use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Resolver\TotalResolver;
use Marello\Bundle\TaxBundle\Tests\ResultComparatorTrait;

class TotalResolverTest extends \PHPUnit_Framework_TestCase
{
    use ResultComparatorTrait;

    /** @var TotalResolver */
    protected $resolver;

    protected function setUp()
    {
        $this->resolver = new TotalResolver();
    }

    public function testResolveEmptyItems()
    {
        $taxable = new Taxable();

        $this->resolver->resolve($taxable);

        $this->assertInstanceOf(Result::class, $taxable->getResult());
        $this->assertInstanceOf(ResultElement::class, $taxable->getResult()->getTotal());
        $this->compareResult([], $taxable->getResult());
    }

    /**
     * @param array $items
     * @param ResultElement $shippingResult
     * @param ResultElement $expectedTotalResult
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        array $items,
        ResultElement $shippingResult = null,
        ResultElement $expectedTotalResult
    ) {
        $taxable = new Taxable();
        if ($shippingResult) {
            $taxable->getResult()->offsetSet(Result::SHIPPING, $shippingResult);
        }
        foreach ($items as $item) {
            $itemTaxable = new Taxable();
            $itemTaxable->setResult(new Result($item));
            $taxable->addItem($itemTaxable);
        }

        $this->resolver->resolve($taxable);

        $this->assertInstanceOf(Result::class, $taxable->getResult());
        $this->assertInstanceOf(ResultElement::class, $taxable->getResult()->getTotal());
        $this->assertEquals($expectedTotalResult, $taxable->getResult()->getTotal());
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function resolveDataProvider()
    {
        return [
            'plain' => [
                'items' => [
                    [
                        Result::ROW => ResultElement::create('24.1879', '19.99', '4.1979'),
                    ],
                ],
                'shippingResult' => ResultElement::create('0', '0'),
                'expectedTotalResult' => ResultElement::create('24.1879', '19.99', '4.1979'),
            ],
            'multiple items same tax' => [
                'items' => [
                    [
                        Result::ROW => ResultElement::create('21.5892', '19.99', '1.5992'),
                    ],
                    [
                        Result::ROW => ResultElement::create('23.7492', '21.99', '1.7592'),
                    ],
                    [
                        Result::ROW => ResultElement::create('25.9092', '23.99', '1.9192'),
                    ],
                ],
                'shippingResult' => ResultElement::create('0', '0'),
                'expectedTotalResult' => ResultElement::create('71.2476', '65.97', '5.2776'),
            ],
            'failed' => [
                'items' => [
                    [
                        Result::ROW => ResultElement::create('', ''),
                    ],
                ],
                'shippingResult' => ResultElement::create('0', '0'),
                'expectedTotalResult' => ResultElement::create('0', '0', '0'),
            ],
            'safe if row failed' => [
                'items' => [
                    [
                        Result::ROW => ResultElement::create('21.59', '19.99', '1.6'),
                    ],
                    [
                        Result::ROW => ResultElement::create('', '23.99', '1.92'),
                    ],
                ],
                'shippingResult' => ResultElement::create('0', '0'),
                'expectedTotalResult' => ResultElement::create('21.59', '19.99', '1.6'),
            ],
            'no shipping taxes' => [
                'items' => [
                    [
                        Result::ROW => ResultElement::create('21.50', '20.00', '1.50'),
                    ],
                ],
                'shippingResult' => null,
                'expectedTotalResult' => ResultElement::create('21.50', '20.00', '1.50'),
            ],
        ];
    }
}
