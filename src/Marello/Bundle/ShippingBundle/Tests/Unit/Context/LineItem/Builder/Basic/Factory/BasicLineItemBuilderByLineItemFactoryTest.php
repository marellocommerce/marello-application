<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context\LineItem\Builder\Basic\Factory;

use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\BasicShippingLineItemBuilder;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\Factory\BasicLineItemBuilderByLineItemFactory;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\ShippingLineItemBuilderFactoryInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\ShippingBundle\Tests\Unit\Context\AbstractShippingLineItemTest;

class BasicLineItemBuilderByLineItemFactoryTest extends AbstractShippingLineItemTest
{
    /**
     * @var ShippingLineItemBuilderFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lineItemBuilderFactory;

    /**
     * @var BasicLineItemBuilderByLineItemFactory
     */
    private $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->lineItemBuilderFactory = $this->createMock(ShippingLineItemBuilderFactoryInterface::class);

        $this->factory = new BasicLineItemBuilderByLineItemFactory($this->lineItemBuilderFactory);
    }

    public function testCreate()
    {
        $lineItem = new ShippingLineItem($this->getShippingLineItemParams());

        $builder = new BasicShippingLineItemBuilder(
            $lineItem->getQuantity(),
            $lineItem->getProductHolder()
        );

        $this->lineItemBuilderFactory
            ->method('createBuilder')
            ->willReturn($builder);

        $builder = $this->factory->createBuilder($lineItem);

        $this->assertEquals($lineItem, $builder->getResult());
    }
}
