<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\ProductBundle\Entity\Variant;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

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
            ['prices', new ProductPrice()],
            ['channels', new SalesChannel()],
            ['channelPrices', new ProductChannelPrice()],
            ['suppliers', new ProductSupplierRelation()],
            ['salesChannelTaxCodes', new ProductChannelTaxRelation()],
        ]);
    }

    /**
     * Test the getPrice of product to returnt the first price
     * of the ProductPrices Collection
     */
    public function testGetFirstPriceFromCollection()
    {
        /** @var ProductPrice $firstProductPrice */
        $firstProductPrice = $this->getEntity(
            ProductPrice::class,
            [
                'id' => 1,
                'value' => 10,
                'currency' => 'EUR',
                'product' => $this->entity
            ]);

        /** @var ProductPrice $secondProductPrice */
        $secondProductPrice = $this->getEntity(
            ProductPrice::class,
            [
                'id' => 2,
                'value' => 15,
                'currency' => 'EUR',
                'product' => $this->entity
            ]);

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
            ]);

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
