<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context\Builder\Basic\Factory;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PaymentBundle\Context\Builder\Basic\BasicPaymentContextBuilder;
use Marello\Bundle\PaymentBundle\Context\Builder\Basic\Factory\BasicPaymentContextBuilderFactory;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory\PaymentLineItemCollectionFactoryInterface;

class BasicPaymentContextBuilderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentLineItemCollectionFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lineItemsCollectionFactoryMock;
    /**
     * @var Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sourceEntityMock;

    protected function setUp(): void
    {
        $this->lineItemsCollectionFactoryMock = $this->createMock(PaymentLineItemCollectionFactoryInterface::class);
        $this->sourceEntityMock = $this->createMock(Order::class);
    }

    public function testCreateBuilder()
    {
        $entityId = '12';

        $builderFactory = new BasicPaymentContextBuilderFactory($this->lineItemsCollectionFactoryMock);

        $builder = $builderFactory->createPaymentContextBuilder(
            $this->sourceEntityMock,
            $entityId
        );

        $expectedBuilder = new BasicPaymentContextBuilder(
            $this->sourceEntityMock,
            $entityId,
            $this->lineItemsCollectionFactoryMock
        );

        $this->assertEquals($expectedBuilder, $builder);
    }
}
