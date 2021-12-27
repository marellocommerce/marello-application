<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Resolver;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Resolver\CustomerAddressResolver;
use Marello\Bundle\TaxBundle\Resolver\CustomerAddressItemResolver;

class CustomerAddressResolverTest extends TestCase
{
    /**
     * @var CustomerAddressResolver
     */
    protected $resolver;

    /**
     * @var CustomerAddressItemResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemResolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->itemResolver = $this->getMockBuilder(CustomerAddressItemResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new CustomerAddressResolver($this->itemResolver);
    }

    public function testEmptyCollection()
    {
        $this->itemResolver->expects($this->never())->method($this->anything());

        $this->resolver->resolve(new Taxable());
    }

    public function testResolveCollection()
    {
        $taxable = new Taxable();
        $taxableItem = new Taxable();
        $taxable->addItem($taxableItem);

        $this->itemResolver->expects($this->once())->method('resolve')->with(
            $this->callback(
                function ($dispatchedTaxable) use ($taxableItem) {
                    $this->assertSame($taxableItem, $dispatchedTaxable);

                    return true;
                }
            )
        );

        $this->resolver->resolve($taxable);
    }
}
