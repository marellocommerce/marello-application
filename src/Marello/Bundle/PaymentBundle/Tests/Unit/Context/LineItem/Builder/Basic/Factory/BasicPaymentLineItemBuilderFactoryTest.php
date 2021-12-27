<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context\LineItem\Builder\Basic\Factory;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Basic\BasicPaymentLineItemBuilder;
use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Basic\Factory\BasicPaymentLineItemBuilderFactory;

class BasicPaymentLineItemBuilderFactoryTest extends \PHPUnit\Framework\TestCase
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

        $builderFactory = new BasicPaymentLineItemBuilderFactory();

        $builder = $builderFactory->createBuilder(
            $quantity,
            $this->productHolderMock
        );

        $expectedBuilder = new BasicPaymentLineItemBuilder(
            $quantity,
            $this->productHolderMock
        );

        $this->assertEquals($expectedBuilder, $builder);
    }
}
