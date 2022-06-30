<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Twig;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;

use Marello\Bundle\ProductBundle\Twig\ProductUnitExtension;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadProductUnitData;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ProductUnitExtensionTest extends WebTestCase
{
    /**
     * @var ProductUnitExtension
     */
    protected $extension;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelperMock */
    protected $doctrineHelperMock;
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        $this->extension = new ProductUnitExtension($this->doctrineHelperMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->extension);
    }

    /**
     * {@inheritdoc}
     */
    public function testNameIsCorrectlySetAndReturnedFromConstant()
    {
        $this->assertEquals(ProductUnitExtension::NAME, $this->extension->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFunctionsAreRegisteredInExtension()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'get_product_unit_value_by_id'
        );

        foreach ($functions as $function) {
            $this->assertInstanceOf(TwigFunction::class, $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testGetFiltersAreRegisteredInExtension()
    {
        $filters = $this->extension->getFilters();
        $this->assertCount(1, $filters);

        $expectedFilters = array(
            'marello_format_product_unit'
        );

        foreach ($filters as $filter) {
            $this->assertInstanceOf(TwigFilter::class, $filter);
            $this->assertContains($filter->getName(), $expectedFilters);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function testNoProductUnitIdIsGiven()
    {
         $this->doctrineHelperMock
            ->expects($this->never())
            ->method('getEntityRepositoryForClass');

        self::assertNull($this->extension->getProductUnitValueById(null));
    }

    /**
     * {@inheritdoc}
     */
    public function testProductUnitIsNotFound()
    {
        $productUnitClass = ExtendHelper::buildEnumValueClassName(LoadProductUnitData::PRODUCT_UNIT_ENUM_CLASS);
        $entityRepositoryMock = $this->createMock(EntityRepository::class);
        $this->doctrineHelperMock
            ->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with($productUnitClass)
            ->willReturn($entityRepositoryMock);

        $entityRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with('someUnit')
            ->willReturn(null);

        self::assertNull($this->extension->getProductUnitValueById('someUnit'));
    }

    /**
     * {@inheritdoc}
     */
    public function testProductUnitIsFoundAndValue()
    {
        $productUnitClass = ExtendHelper::buildEnumValueClassName(LoadProductUnitData::PRODUCT_UNIT_ENUM_CLASS);
        $productUnitEnumValue =  new TestEnumValue('someUnit', 'someUnit');
        $entityRepositoryMock = $this->createMock(EntityRepository::class);
        $this->doctrineHelperMock
            ->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with($productUnitClass)
            ->willReturn($entityRepositoryMock);

        $entityRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with('someUnit')
            ->willReturn($productUnitEnumValue);

        self::assertEquals($productUnitEnumValue->getName(), $this->extension->getProductUnitValueById('someUnit'));
    }

    /**
     * {@inheritdoc}
     */
    public function testFormatProductUnitWithoutCorrectValue()
    {
        self::assertNull($this->extension->formatProductUnit(null));
    }

    /**
     * {@inheritdoc}
     */
    public function testFormatProductUnitValue()
    {
        $productUnitEnumValue =  new TestEnumValue('someUnit', 'someUnit');
        self::assertEquals($productUnitEnumValue->getName(), $this->extension->formatProductUnit($productUnitEnumValue));
    }
}
