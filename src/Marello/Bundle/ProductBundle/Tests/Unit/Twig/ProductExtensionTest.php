<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Twig\ProductExtension;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductExtensionTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChannelProvider
     */
    protected $channelProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CategoriesIdsProvider
     */
    protected $categoryIdsProvider;

    /**
     * @var ProductExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
    protected function tearDown(): void
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
        $this->assertCount(3, $functions);

        $expectedFunctions = array(
            'marello_sales_get_saleschannel_ids',
            'marello_product_get_categories_ids',
            'marello_get_product_by_sku'
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

    /**
     * Test get product via simple twig function by product SKU
     */
    public function testGetProductBySku()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper $doctrineHelperMock */
        $doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|ProductRepository $productRepositoryMock */
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->extension->setOroEntityDoctrineHelper($doctrineHelperMock);
        $productSku = 'sku123';
        $doctrineHelperMock->expects(static::once())
            ->method('getEntityRepository')
            ->with(Product::class)
            ->willReturn($productRepositoryMock);

        $productRepositoryMock->expects(static::once())
            ->method('findOneBySku')
            ->with($productSku)
            ->willReturn($this->createMock(Product::class));

        $result = $this->extension->getProductBySku($productSku);
        self::assertNotNull($result);
        self::assertInstanceOf(Product::class, $result);
    }

    /**
     * Test extension will return null when doctrine helper is not set
     */
    public function testDoctrineHelperNotSet()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper $doctrineHelperMock */
        $doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|ProductRepository $productRepositoryMock */
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $productSku = 'sku123';
        $doctrineHelperMock->expects(static::never())
            ->method('getEntityRepository');

        $productRepositoryMock->expects(static::never())
            ->method('findOneBySku');

        $result = $this->extension->getProductBySku($productSku);
        self::assertNull($result);
    }

    /**
     * Test null is returned when no product SKU is given
     */
    public function testProductSkuIsNull()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper $doctrineHelperMock */
        $doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|ProductRepository $productRepositoryMock */
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->extension->setOroEntityDoctrineHelper($doctrineHelperMock);
        $productSku = null;
        $doctrineHelperMock->expects(static::never())
            ->method('getEntityRepository');

        $productRepositoryMock->expects(static::never())
            ->method('findOneBySku');

        $result = $this->extension->getProductBySku($productSku);
        self::assertNull($result);
    }

    /**
     * Test null is returned when no product is found for the given SKU
     */
    public function testProductIsNotFoundForGivenSku()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper $doctrineHelperMock */
        $doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|ProductRepository $productRepositoryMock */
        $productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->extension->setOroEntityDoctrineHelper($doctrineHelperMock);
        $productSku = 'sku123';
        $doctrineHelperMock->expects(static::once())
            ->method('getEntityRepository')
            ->with(Product::class)
            ->willReturn($productRepositoryMock);

        $productRepositoryMock->expects(static::once())
            ->method('findOneBySku')
            ->with($productSku)
            ->willReturn(null);

        $result = $this->extension->getProductBySku($productSku);
        self::assertNull($result);
    }
}
