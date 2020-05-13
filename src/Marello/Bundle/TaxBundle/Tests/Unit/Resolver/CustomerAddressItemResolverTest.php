<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Resolver;

use Marello\Bundle\TaxBundle\Model\ResultElement;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Resolver\CustomerAddressItemResolver;

class CustomerAddressItemResolverTest extends AbstractItemResolverTestCase
{
    /** @var CustomerAddressItemResolver */
    protected $resolver;

    /** {@inheritdoc} */
    protected function createResolver()
    {
        return new CustomerAddressItemResolver($this->rowTotalResolver, $this->matcher);
    }

    /** {@inheritdoc} */
    protected function getTaxable()
    {
        return new Taxable();
    }

    public function testItemNotApplicable()
    {
        $taxable = new Taxable();
        $taxable->addItem(new Taxable());

        $this->assertNothing();

        $this->resolver->resolve($taxable);

        $this->assertEmptyResult($taxable);
    }

    /** {@inheritdoc} */
    public function rulesDataProvider()
    {
        return [
            [
                '19.99',
                $this->getTaxRule('city', '0.08'),
            ],
            [
                '19.99',
                $this->getTaxRule('region', '0.07'),
            ],
        ];
    }

    /** {@inheritdoc} */
    protected function assertEmptyResult(Taxable $taxable)
    {
        $this->assertEquals(new ResultElement(), $taxable->getResult()->getRow());
    }
}
