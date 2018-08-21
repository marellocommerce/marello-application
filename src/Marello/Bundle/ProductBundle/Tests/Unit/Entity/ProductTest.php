<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\ProductBundle\Entity\Variant;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;
    use EntityTestCaseTrait;

    /**
     * @var Product $entity
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new Product();
    }

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new Product(), [
            ['id', 42],
            ['name', 'some string'],
            ['sku', 'some string'],
            ['manufacturingCode', 'some string'],
            ['status', new ProductStatus('active')],
            ['type', 'some string'],
            ['cost', 'some string'],
            ['weight', 3.1415926],
            ['warranty', 42],
            ['organization', new Organization()],
            ['variant', new Variant()],
            ['data', []],
            ['preferredSupplier', new Supplier()],
            ['taxCode', new TaxCode()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
        $this->assertPropertyCollections(new Product(), [
            ['prices', new AssembledPriceList()],
            ['channels', new SalesChannel()],
            ['channelPrices', new AssembledChannelPriceList()],
            ['suppliers', new ProductSupplierRelation()],
            ['salesChannelTaxCodes', new ProductChannelTaxRelation()],
        ]);
    }

    public function testGetPrice()
    {
        $price1 = new AssembledPriceList();
        $price2 = new AssembledPriceList();

        $product = new Product();

        $product
            ->addPrice($price1)
            ->addPrice($price2);

        static::assertEquals($price1, $product->getPrice());
        $product->removePrice($price1);
        static::assertEquals($price2, $product->getPrice());
    }

    /**
     * Test the getPrice of product to return the first price
     * of the ProductPrices Collection
     */
    public function testGetFirstPriceFromCollection()
    {
        $firstProductPrice = new AssembledPriceList();
        $secondProductPrice = new AssembledPriceList();
        $this->entity
            ->addPrice($firstProductPrice)
            ->addPrice($secondProductPrice);

        static::assertEquals($firstProductPrice, $this->entity->getPrice());
    }

    /**
     * @dataProvider getSalesChannelTaxCodeDataProvider
     *
     * @param SalesChannel $productChannel
     * @param SalesChannel $estimationChannel
     * @param TaxCode $channelTaxCode
     * @param TaxCode|null $expectedTaxCode
     */
    public function testGetSalesChannelTaxCode(
        SalesChannel $productChannel,
        SalesChannel $estimationChannel,
        TaxCode $channelTaxCode,
        $expectedTaxCode
    ) {
        /** @var TaxCode $defaultTaxCode */
        $defaultTaxCode = $this->getEntity(TaxCode::class, ['id' => 3, 'code' => 'TAX_CODE_DEFAULT']);
        /** @var ProductChannelTaxRelation $productChannelTaxRelation */
        $productChannelTaxRelation = $this->getEntity(
            ProductChannelTaxRelation::class,
            [
                'id' => 1,
                'salesChannel' => $productChannel,
                'taxCode' => $channelTaxCode
            ]
        );

        $this->entity
            ->setTaxCode($defaultTaxCode)
            ->addSalesChannelTaxCode($productChannelTaxRelation);

        static::assertEquals($expectedTaxCode, $this->entity->getSalesChannelTaxCode($estimationChannel));
    }

    /**
     * @return array
     */
    public function getSalesChannelTaxCodeDataProvider()
    {
        $validChannel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        $notValidChannel = $this->getEntity(SalesChannel::class, ['id' => 2, 'currency' => 'EUR']);
        $channelTaxCode = $this->getEntity(TaxCode::class, ['id' => 1, 'code' => 'TAX_CODE_CHANNEL']);
        return [
            'withChannelTaxCode' => [
                'productChannel' => $validChannel,
                'estimationChannel' => $validChannel,
                'channelTaxCode' => $channelTaxCode,
                'expectedTaxCode' => $channelTaxCode,
            ],
            'noChannelTaxCode' => [
                'productChannel' => $validChannel,
                'estimationChannel' => $notValidChannel,
                'channelTaxCode' => $channelTaxCode,
                'expectedTaxCode' => null,
            ],
        ];
    }
}
