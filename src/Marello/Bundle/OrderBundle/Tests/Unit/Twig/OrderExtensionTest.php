<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\OrderBundle\Provider\OrderItem\ShippingPreparedOrderItemsForNotificationProvider;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use Marello\Bundle\OrderBundle\Twig\OrderExtension;
use Marello\Bundle\OrderBundle\Entity\Order;

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
        $registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->workflowManager = $this
            ->getMockBuilder(WorkflowManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemsForNotificationProvider = $this
            ->getMockBuilder(ShippingPreparedOrderItemsForNotificationProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new OrderExtension($registry, $this->workflowManager, $orderItemsForNotificationProvider);
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

        $expectedFunctions = [
            'marello_order_can_return',
            'marello_order_item_shipped',
            'marello_get_order_item_status',
            'marello_get_order_items_for_notification'
        ];

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

        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->getMockBuilder(WorkflowItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var WorkflowStep $workflowStep */
        $workflowStep = $this->getMockBuilder(WorkflowStep::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflowManager
            ->expects($this->once())
            ->method('getWorkflowItemsByEntity')
            ->with($order)
            ->willReturn([$workflowItem]);

        $workflowItem->expects($this->atLeastOnce())
            ->method('getCurrentStep')
            ->willReturn($workflowStep);

        $workflowStep->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('shipped');

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

        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->getMockBuilder(WorkflowItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var WorkflowStep $workflowStep */
        $workflowStep = $this->getMockBuilder(WorkflowStep::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflowManager
            ->expects($this->once())
            ->method('getWorkflowItemsByEntity')
            ->with($order)
            ->willReturn([]);

        $this->assertFalse($this->extension->canReturn($order));
    }
}
