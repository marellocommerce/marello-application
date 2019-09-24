<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Twig\OrderExtension;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\OrderBundle\Provider\OrderItem\ShippingPreparedOrderItemsForNotificationProvider;

class OrderExtensionTest extends WebTestCase
{
    /**
     * @var WorkflowManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowManager;

    /**
     * @var OrderExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->workflowManager = $this->createMock(WorkflowManager::class);

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

        $this->extension = new OrderExtension($this->workflowManager);
        $this->extension
            ->setItemsForNotificationProvider($orderItemsForNotificationProvider)
            ->setRegistry($registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
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
        $this->assertCount(4, $functions);

        $expectedFunctions = array(
            'marello_order_can_return',
            'marello_order_item_shipped',
            'marello_get_order_item_status',
            'marello_get_order_items_for_notification'
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
        /** @var Order $order */
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItem = new OrderItem();
        $orderItem->setStatus(LoadOrderItemStatusData::SHIPPED);
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
        /** @var Order $order */
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItem = new OrderItem();
        $orderItem->setStatus(LoadOrderItemStatusData::PROCESSING);
        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItem]);

        $this->assertFalse($this->extension->canReturn($order));
    }
}
