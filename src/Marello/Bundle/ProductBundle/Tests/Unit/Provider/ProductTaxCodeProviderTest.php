<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\ProductBundle\Provider\ProductTaxCodeProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Component\Testing\Unit\EntityTrait;

class ProductTaxCodeProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var ProductTaxCodeProvider
     */
    protected $productTaxCodeProvider;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->productTaxCodeProvider = new ProductTaxCodeProvider($this->registry);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param TaxCode|null $channelTaxCode
     * @param TaxCode $defaultTaxCode
     * @param string $expectedValue
     */
    public function testGetData($channelTaxCode, TaxCode $defaultTaxCode, $expectedValue)
    {
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->createMock(Product::class);
        $product->expects(static::any())
            ->method('getId')
            ->willReturn(1);
        $product->expects(static::once())
            ->method('getSalesChannelTaxCode')
            ->with($channel)
            ->willReturn($channelTaxCode);
        $product->expects(static::any())
            ->method('getTaxCode')
            ->willReturn($defaultTaxCode);

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository
            ->expects(static::once())
            ->method('findBySalesChannel')
            ->with($channel->getId(), [$product->getId()])
            ->willReturn([$product]);

        $salesChannelRepository = $this->createMock(EntityRepository::class);
        $salesChannelRepository
            ->expects(static::once())
            ->method('find')
            ->with($channel->getId())
            ->willReturn($channel);

        $this->registry
            ->expects(static::at(0))
            ->method('getManagerForClass')
            ->with(Product::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(1))
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepository);

        $this->registry
            ->expects(static::at(2))
            ->method('getManagerForClass')
            ->with(SalesChannel::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(3))
            ->method('getRepository')
            ->with(SalesChannel::class)
            ->willReturn($salesChannelRepository);

        $expectedData = [sprintf('%s%s', ProductTaxCodeProvider::IDENTIFIER_PREFIX, $product->getId()) =>
            $expectedValue,
        ];
        static::assertEquals(
            $expectedData,
            $this->productTaxCodeProvider->getData($channel->getId(), [$product->getId()])
        );
    }

    public function getDataDataProvider()
    {
        $defaultTaxCode = $this->getEntity(TaxCode::class, ['id' => 1, 'code' => 'TAX_DEFAULT']);

        return [
            'noChannelTaxCode' => [
                'channelTaxCode' => null,
                'defaultTaxCode' => $defaultTaxCode,
                'expectedValue' => ['id' => 1, 'code' => 'TAX_DEFAULT']
            ],
            'withChannelTaxCode' => [
                'channelTaxCode' => $this->getEntity(TaxCode::class, ['id' => 2, 'code' => 'TAX_CHANNEL']),
                'defaultTaxCode' => $defaultTaxCode,
                'expectedValue' => ['id' => 2, 'code' => 'TAX_CHANNEL']
            ]
        ];
    }
}
