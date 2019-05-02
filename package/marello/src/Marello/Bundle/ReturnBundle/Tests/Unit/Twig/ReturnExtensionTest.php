<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Twig;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\ReturnBundle\Twig\ReturnExtension;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class ReturnExtensionTest extends WebTestCase
{
    /**
     * @var ReturnHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var WorkflowManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowManager;

    /**
     * @var ReturnExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(ReturnHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->workflowManager = $this->getMockBuilder(WorkflowManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new ReturnExtension($this->helper, $this->workflowManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->extension);
        unset($this->helper);
        unset($this->workflowManager);
    }

    /**
     * {@inheritdoc}
     */
    public function testNameIsCorrectlySetAndReturnedFromConstant()
    {
        $this->assertEquals(ReturnExtension::NAME, $this->extension->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFunctionsAreRegisteredInExtension()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(2, $functions);

        $expectedFunctions = array(
            'marello_return_get_order_item_returned_quantity',
            'marello_return_is_on_hold'
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
    public function testGetOrderItemReturnedQuantity()
    {
        /** @var OrderItem $orderItem */
        $orderItem = $this->getMockBuilder(OrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper->expects($this->once())
            ->method('getOrderItemReturnedQuantity')
            ->with($orderItem);

        $this->assertEquals(0, $this->extension->getOrderItemReturnedQuantity($orderItem));
    }
}
