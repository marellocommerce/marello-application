<?php

namespace Marello\Bundle\SupplierBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Twig\SupplierExtension;
use Marello\Bundle\SupplierBundle\Provider\SupplierProvider;

class SupplierExtensionTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $supplierProvider;

    /**
     * @var SupplierExtension
     */
    protected $extension;

    protected function setUp(): void
    {
        $this->supplierProvider = $this->getMockBuilder(SupplierProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new SupplierExtension($this->supplierProvider);
    }

    protected function tearDown(): void
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
