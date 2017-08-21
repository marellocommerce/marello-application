<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Resolver;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Resolver\AbstractItemResolver;
use Marello\Bundle\TaxBundle\Resolver\RowTotalResolver;
use Marello\Bundle\TaxBundle\Tests\ResultComparatorTrait;

abstract class AbstractItemResolverTestCase extends \PHPUnit_Framework_TestCase
{
    use ResultComparatorTrait;

    /** @var AbstractItemResolver */
    protected $resolver;

    /**
     * @var RowTotalResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowTotalResolver;

    /**
     * @var TaxRuleMatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $matcher;

    protected function setUp()
    {
        $this->rowTotalResolver = $this->getMockBuilder(RowTotalResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->matcher = $this->createMock(TaxRuleMatcherInterface::class);

        $this->resolver = $this->createResolver();
    }

    /** @return AbstractItemResolver */
    abstract protected function createResolver();

    protected function assertNothing()
    {
        $this->matcher->expects($this->never())->method($this->anything());
        $this->rowTotalResolver->expects($this->never())->method($this->anything());
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

    /**
     * @return Taxable
     */
    abstract protected function getTaxable();

    /**
     * @param Taxable $taxable
     */
    abstract protected function assertEmptyResult(Taxable $taxable);

    public function testTaxationAddressMissing()
    {
        $taxable = $this->getTaxable();
        $taxable->setPrice('1');
        $taxable->setAmount('1');

        $this->assertNothing();

        $this->resolver->resolve($taxable);

        $this->assertEmptyResult($taxable);
    }

    public function testEmptyAmount()
    {
        $taxable = $this->getTaxable();

        $this->assertNothing();

        $this->resolver->resolve($taxable);

        $this->assertEmptyResult($taxable);
    }

    public function testEmptyRules()
    {
        $taxable = $this->getTaxable()
            ->setTaxationAddress(new MarelloAddress())
            ->setPrice('1')
            ->setAmount('1')
            ->setTaxCode(new TaxCode());

        $taxableUnitPrice = (float)$taxable->getPrice();
        $taxableAmount = $taxableUnitPrice * (float)$taxable->getQuantity();

        $this->matcher->expects($this->once())->method('match')->willReturn(null);

        $this->rowTotalResolver->expects($this->once())
            ->method('resolveRowTotal')
            ->with($taxable->getResult(), 1, $taxableAmount);

        $this->resolver->resolve($taxable);

        $this->assertEquals([], $taxable->getResult()->jsonSerialize());
    }

    /**
     * @dataProvider rulesDataProvider
     * @param string $taxableAmount
     * @param TaxRule $taxRule
     */
    public function testRules($taxableAmount, TaxRule $taxRule)
    {
        $taxable = $this->getTaxable()
            ->setPrice($taxableAmount)
            ->setQuantity(3)
            ->setAmount($taxableAmount)
            ->setTaxationAddress(new MarelloAddress())
            ->setTaxCode(new TaxCode());
        

        $taxableUnitPrice = (float)$taxable->getPrice();

        $this->matcher->expects($this->once())->method('match')->willReturn($taxRule);

        $this->rowTotalResolver->expects($this->once())
            ->method('resolveRowTotal')
            ->with($taxable->getResult(), $taxableUnitPrice, $taxable->getQuantity(), $taxRule);

        $this->resolver->resolve($taxable);
    }

    /**
     * @return array
     */
    abstract public function rulesDataProvider();
}
