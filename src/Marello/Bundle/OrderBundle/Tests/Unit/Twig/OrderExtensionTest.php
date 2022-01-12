<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\OrderBundle\Provider\OrderItem\ShippingPreparedOrderItemsForNotificationProvider;
use Marello\Bundle\OrderBundle\Twig\OrderExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Marello\Bundle\OrderBundle\Tests\Unit\Stub\StatusEnumClassStub;

class OrderExtensionTest extends WebTestCase
{
    /**
     * @var OrderExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->extension = new OrderExtension();
        /** @var Registry $registry */
        $registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ShippingPreparedOrderItemsForNotificationProvider $orderItemsForNotificationProvider */
        $orderItemsForNotificationProvider = $this
            ->getMockBuilder(ShippingPreparedOrderItemsForNotificationProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension
            ->setItemsForNotificationProvider($orderItemsForNotificationProvider)
            ->setRegistry($registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->extension);
        unset($this->workflowManager);
    }

    /**
     * {@inheritdoc}
     */
    public function testNameIsCorrectlySetAndReturnedFromConstant()
    {
        $this->assertEquals(OrderExtension::NAME, $this->extension->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFunctionsAreRegisteredInExtension()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(6, $functions);

        $expectedFunctions = array(
            'marello_order_can_return',
            'marello_order_item_shipped',
            'marello_get_order_item_status',
            'marello_get_order_items_for_notification',
            'marello_get_order_total_paid',
            'marello_get_order_total_due'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testIfOrderCanReturnIsTrue()
    {
        /** @var Order|\PHPUnit\Framework\MockObject\MockObject $order */
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderItemStatusMock = $this->createMock(StatusEnumClassStub::class);
        $orderItemStatusMock
            ->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn(LoadOrderItemStatusData::SHIPPED);

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem
            ->expects(static::atLeastOnce())
            ->method('getStatus')
            ->willReturn($orderItemStatusMock);
        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItem]);

        $this->assertTrue($this->extension->canReturn($order));
    }

    /**
     * {@inheritdoc}
     */
    public function testIfOrderCanReturnIsFalse()
    {
        /** @var Order|\PHPUnit\Framework\MockObject\MockObject $order */
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderItemStatusMock = $this->createMock(StatusEnumClassStub::class);
        $orderItemStatusMock
            ->expects(static::atLeastOnce())
            ->method('getId')
            ->willReturn(LoadOrderItemStatusData::PROCESSING);

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem
            ->expects(static::atLeastOnce())
            ->method('getStatus')
            ->willReturn($orderItemStatusMock);

        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItem]);

        $this->assertFalse($this->extension->canReturn($order));
    }
}
