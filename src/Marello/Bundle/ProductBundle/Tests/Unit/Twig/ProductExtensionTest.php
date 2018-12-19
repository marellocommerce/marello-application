<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Twig\ProductExtension;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider;

class ProductExtensionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ChannelProvider
     */
    protected $channelProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CategoriesIdsProvider
     */
    protected $categoryIdsProvider;

    /**
     * @var ProductExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->channelProvider = $this->getMockBuilder(ChannelProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryIdsProvider = $this->getMockBuilder(CategoriesIdsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new ProductExtension(
            $this->channelProvider,
            $this->categoryIdsProvider
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->extension);
        unset($this->channelProvider);
        unset($this->categoryIdsProvider);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetName()
    {
        $this->assertEquals(ProductExtension::NAME, $this->extension->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(2, $functions);

        $expectedFunctions = array(
            'marello_sales_get_saleschannel_ids',
            'marello_product_get_categories_ids'
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
    public function testGetSalesChannelsIds()
    {
        /** @var Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->channelProvider->expects(static::atLeastOnce())
            ->method('getSalesChannelsIds')
            ->willReturn([]);

        $this->assertCount(0, $this->extension->getSalesChannelsIds($product));
    }


    /**
     * {@inheritdoc}
     */
    public function testGetCategoryIds()
    {
        /** @var Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryIdsProvider->expects(static::once())
            ->method('getCategoriesIds')
            ->willReturn([]);

        $this->assertCount(0, $this->extension->getCategoriesIds($product));
    }
}
