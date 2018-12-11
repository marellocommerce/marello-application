<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Twig\ProductExtension;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;

class ProductExtensionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ChannelProvider
     */
    protected $channelProvider;

    /**
     * @var ProductExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->channelProvider = $this->getMockBuilder(ChannelProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new ProductExtension($this->channelProvider);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->channelProvider);
    }

    public function testGetName()
    {
        $this->assertEquals(ProductExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_sales_get_saleschannel_ids'
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    public function testGetSalesChannelsIds()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(0, $this->extension->getSalesChannelsIds($product));
    }
}
