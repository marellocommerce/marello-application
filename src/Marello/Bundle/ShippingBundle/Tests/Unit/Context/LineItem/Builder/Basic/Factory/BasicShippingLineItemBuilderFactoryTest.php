<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context\LineItem\Builder\Basic\Factory;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\BasicShippingLineItemBuilder;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\Factory\BasicShippingLineItemBuilderFactory;

class BasicShippingLineItemBuilderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productHolderMock;

    public function setUp(): void
    {
        $this->productHolderMock = $this->createMock(OrderItem::class);
    }

    public function testCreate()
    {
        $quantity = 15;

        $builderFactory = new BasicShippingLineItemBuilderFactory();

        $builder = $builderFactory->createBuilder(
            $quantity,
            $this->productHolderMock
        );

        $expectedBuilder = new BasicShippingLineItemBuilder(
            $quantity,
            $this->productHolderMock
        );

        $this->assertEquals($expectedBuilder, $builder);
    }
}
