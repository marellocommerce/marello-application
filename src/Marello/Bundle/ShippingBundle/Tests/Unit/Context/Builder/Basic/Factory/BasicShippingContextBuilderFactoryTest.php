<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context\Builder\Basic\Factory;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ShippingBundle\Context\Builder\Basic\BasicShippingContextBuilder;
use Marello\Bundle\ShippingBundle\Context\Builder\Basic\Factory\BasicShippingContextBuilderFactory;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;

class BasicShippingContextBuilderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingLineItemCollectionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lineItemsCollectionMock;

    /**
     * @var Price|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subtotalMock;

    /**
     * @var Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sourceEntityMock;

    protected function setUp(): void
    {
        $this->lineItemsCollectionMock = $this->createMock(ShippingLineItemCollectionInterface::class);
        $this->subtotalMock = $this->createMock(Price::class);
        $this->sourceEntityMock = $this->createMock(Order::class);
    }

    public function testCreateBuilder()
    {
        $entityId = '12';

        $builderFactory = new BasicShippingContextBuilderFactory();

        $builder = $builderFactory->createShippingContextBuilder(
            $this->sourceEntityMock,
            $entityId
        );

        $expectedBuilder = new BasicShippingContextBuilder(
            $this->sourceEntityMock,
            $entityId
        );

        $this->assertEquals($expectedBuilder, $builder);
    }
}
