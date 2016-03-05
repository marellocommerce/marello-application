<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Twig;

use Marello\Bundle\ReturnBundle\Twig\ReturnExtension;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class ReturnExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var ReturnExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(ReturnHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new ReturnExtension($this->helper);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->helper);
    }

    public function testGetName()
    {
        $this->assertEquals(ReturnExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_return_get_order_item_returned_quantity'
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
