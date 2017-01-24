<?php

namespace Marello\Bundle\SupplierBundle\Tests\Unit\Twig;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Twig\SupplierExtension;
use Marello\Bundle\SupplierBundle\Provider\SupplierProvider;

class SupplierExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $supplierProvider;

    /**
     * @var SupplierExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->supplierProvider = $this->getMockBuilder(SupplierProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new SupplierExtension($this->supplierProvider);
    }

    protected function tearDown()
    {
        unset($this->extension);
        unset($this->supplierProvider);
    }

    public function testGetName()
    {
        $this->assertEquals(SupplierExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'marello_supplier_get_supplier_ids'
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
        $this->assertEquals(0, $this->extension->getSuppliersIds($product));
    }
}
