<?php

namespace Marello\Bundle\PricingBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Entity\Repository\ProductChannelPriceRepository;
use Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\Testing\Unit\EntityTrait;

class ChannelPriceProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var ChannelPriceProvider
     */
    protected $channelPriceProvider;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->channelPriceProvider = new ChannelPriceProvider($this->registry);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param array $channelPrices
     * @param ProductPrice $defaultPrice
     * @param int $expectedValue
     */
    public function testGetData($channelPrices, ProductPrice $defaultPrice, $expectedValue)
    {
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 1]);

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

        $productPriceRepository = $this->createMock(EntityRepository::class);
        $productPriceRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['product' => $product->getId(), 'currency' => $channel->getCurrency()])
            ->willReturn($defaultPrice);

        $productChannelPriceRepository = $this->createMock(ProductChannelPriceRepository::class);
        $productChannelPriceRepository
            ->expects(static::once())
            ->method('findOneBySalesChannel')
            ->with($channel->getId(), $product->getId())
            ->willReturn($channelPrices);

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

        $this->registry
            ->expects(static::at(4))
            ->method('getManagerForClass')
            ->with(ProductPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(5))
            ->method('getRepository')
            ->with(ProductPrice::class)
            ->willReturn($productPriceRepository);

        $this->registry
            ->expects(static::at(6))
            ->method('getManagerForClass')
            ->with(ProductChannelPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(7))
            ->method('getRepository')
            ->with(ProductChannelPrice::class)
            ->willReturn($productChannelPriceRepository);

        $expectedData = [sprintf('%s%s', ChannelPriceProvider::IDENTIFIER_PREFIX, $product->getId()) => [
            'value' => $expectedValue,
        ]];
        static::assertEquals(
            $expectedData,
            $this->channelPriceProvider->getData($channel->getId(), [$product->getId()])
        );
    }

    public function getDataDataProvider()
    {
        $defaultPrice = $this->getEntity(ProductPrice::class, ['id' => 1, 'value' => 100, 'currency' => 'EUR']);

        return [
            'noChannelPrice' => [
                'channelPrices' => [],
                'defaultPrice' => $defaultPrice,
                'expectedValue' => 100
            ],
            'withChannelPrice' => [
                'channelPrices' => [['price_value' => 50]],
                'defaultPrice' => $defaultPrice,
                'expectedValue' => 50
            ]
        ];
    }

    /**
     * @dataProvider getChannelPriceDataProvider
     *
     * @param array $prices
     * @param array $expectedData
     */
    public function testGetChannelPrice($prices, $expectedData)
    {
        $channel = 1;
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 1]);
        $repository = $this->createMock(ProductChannelPriceRepository::class);

        $this->registry
            ->expects(static::once())
            ->method('getManagerForClass')
            ->with(ProductChannelPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::once())
            ->method('getRepository')
            ->with(ProductChannelPrice::class)
            ->willReturn($repository);
        $repository
            ->expects(static::once())
            ->method('findOneBySalesChannel')
            ->with($channel, $product->getId())
            ->willReturn($prices);

        static::assertEquals($expectedData, $this->channelPriceProvider->getChannelPrice($channel, $product));
    }

    public function getChannelPriceDataProvider()
    {
        return [
            'noPrice' => [
                'prices' => [],
                'expectedData' => ['hasPrice' => false]
            ],
            'onePrice' => [
                'prices' => [['price_value' => 50]],
                'expectedData' => ['hasPrice' => true, 'price' => 50]
            ],
            'twoPrices' => [
                'prices' => [['price_value' => 50], ['price_value' => 60]],
                'expectedData' => ['hasPrice' => false]
            ]
        ];
    }

    /**
     * @dataProvider getDefaultPriceDataProvider
     *
     * @param mixed $price
     * @param int|null $expectedValue
     */
    public function testGetDefaultPrice($price, $expectedValue)
    {
        
        /** @var SalesChannel $channel */
        $channel = $this->getEntity(SalesChannel::class, ['id' => 1, 'currency' => 'EUR']);
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['id' => 1]);
        $salesChannelRepository = $this->createMock(EntityRepository::class);
        $salesChannelRepository
            ->expects(static::once())
            ->method('find')
            ->with($channel->getId())
            ->willReturn($channel);
        $productPriceRepository = $this->createMock(EntityRepository::class);
        $productPriceRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['product' => $product->getId(), 'currency' => $channel->getCurrency()])
            ->willReturn($price);

        $this->registry
            ->expects(static::at(0))
            ->method('getManagerForClass')
            ->with(SalesChannel::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(1))
            ->method('getRepository')
            ->with(SalesChannel::class)
            ->willReturn($salesChannelRepository);

        $this->registry
            ->expects(static::at(2))
            ->method('getManagerForClass')
            ->with(ProductPrice::class)
            ->willReturnSelf();
        $this->registry
            ->expects(static::at(3))
            ->method('getRepository')
            ->with(ProductPrice::class)
            ->willReturn($productPriceRepository);

        static::assertEquals($expectedValue, $this->channelPriceProvider->getDefaultPrice($channel->getId(), $product));
    }

    public function getDefaultPriceDataProvider()
    {
        return [
            'noPriceObject' => [
                'price' => [],
                'expectedValue' => null
            ],
            'correctPriceObject' => [
                'price' => $this->getEntity(ProductPrice::class, ['id' => 1, 'value' => 50, 'currency' => 'EUR']),
                'expectedValue' => 50
            ],
            'notCorrectPriceObject' => [
                'price' => $this->getEntity(Product::class, ['id' => 1]),
                'expectedValue' => null
            ]
        ];
    }
}
