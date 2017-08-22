<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Resolver;

use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Resolver\CurrencyResolver;
use Marello\Bundle\TaxBundle\Tests\ResultComparatorTrait;

class CurrencyResolverTest extends \PHPUnit_Framework_TestCase
{
    use ResultComparatorTrait;

    const CURRENCY = 'USD';

    /** @var CurrencyResolver */
    protected $resolver;

    protected function setUp()
    {
        $this->resolver = new CurrencyResolver();
    }

    public function testResolve()
    {
        $taxable = new Taxable();
        $taxable->setCurrency(self::CURRENCY);
        $taxable->setResult(
            new Result(
                [
                    Result::TOTAL => ResultElement::create('11', '10'),
                ]
            )
        );
        $itemTaxable = new Taxable();
        $itemTaxable->setCurrency(self::CURRENCY);
        $itemTaxable->setResult(
            new Result(
                [
                    Result::ROW => ResultElement::create('11', '10', '1'),
                ]
            )
        );
        $taxable->addItem($itemTaxable);

        $this->resolver->resolve($taxable);

        $resultTotal = ResultElement::create('11', '10')
            ->setCurrency(self::CURRENCY);

        $this->compareResult(
            new Result(
                [
                    Result::TOTAL => $resultTotal,
                ]
            ),
            $taxable->getResult()
        );

        $resultRow = ResultElement::create('11', '10', '1')
            ->setCurrency(self::CURRENCY);

        $this->compareResult(
            new Result(
                [
                    Result::ROW => $resultRow,
                ]
            ),
            $itemTaxable->getResult()
        );
    }
}
