<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Provider;

use Marello\Bundle\ProductBundle\Model\ProductTypeInterface;
use Marello\Bundle\ProductBundle\Provider\ProductTypesProvider;
use \PHPUnit\Framework\TestCase;

class ProductTypesProviderTest extends TestCase
{
    /**
     * @var ProductTypesProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new ProductTypesProvider();
    }

    public function tearDown()
    {
        unset($this->registry);
    }

    public function testAddProductType()
    {
        $productTypeName = 'test';
        $productType = $this->getProductTypeMock($productTypeName);

        $this->provider->addProductType($productType);
        $this->assertCount(1, $this->provider->getProductTypes());
        $this->assertSame($productType, $this->provider->getProductType($productTypeName));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Product Type with name "test" already registered
     */
    public function testAddTwoProductTypesWithSameName()
    {
        $productTypeName = 'test';

        $this->provider->addProductType($this->getProductTypeMock($productTypeName));
        $this->provider->addProductType($this->getProductTypeMock($productTypeName));
    }

    /**
     * @param string $name
     * @return ProductTypeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getProductTypeMock($name)
    {
        $mock = $this->createMock(ProductTypeInterface::class);
        $mock->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }
}
