<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Twig;

use Marello\Bundle\ReturnBundle\Twig\ReturnExtension;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class ReturnExtensionTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var ReturnExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(ReturnHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->workflowManager = $this->client->getKernel()->getContainer()->get('oro_workflow.manager');
        $this->extension = new ReturnExtension($this->helper, $this->workflowManager);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->helper);
        unset($this->workflowManager);
    }

    public function testGetName()
    {
        $this->assertEquals(ReturnExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
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

    public function testGetOrderItemReturnedQuantity()
    {
        $orderItem = $this->getMockBuilder(OrderItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(0, $this->extension->getOrderItemReturnedQuantity($orderItem));
    }
}
