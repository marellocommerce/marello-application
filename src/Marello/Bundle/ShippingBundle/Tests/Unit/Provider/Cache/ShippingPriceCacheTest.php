<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Cache;

use Marello\Bundle\ShippingBundle\Provider\Cache\ShippingPriceCache;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\DoctrineShippingLineItemCollection;
use Marello\Bundle\ShippingBundle\Context\ShippingContext;
use Marello\Bundle\ShippingBundle\Context\ShippingContextCacheKeyGenerator;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

class ShippingPriceCacheTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ShippingPriceCache
     */
    protected $cache;

    /**
     * @var CacheItemPoolInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheProvider;

    /**
     * @var ShippingContextCacheKeyGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $keyGenerator;

    public function setUp(): void
    {
        $this->cacheProvider = $this->createMock(CacheItemPoolInterface::class);

        $this->keyGenerator = $this->createMock(ShippingContextCacheKeyGenerator::class);
        $this->keyGenerator->expects(static::any())
            ->method('generateKey')
            ->will(static::returnCallback(function (ShippingContextInterface $context) {
                return ($context->getSourceEntity() ? get_class($context->getSourceEntity()) : '')
                    .'_'.$context->getSourceEntityIdentifier();
            }));

        $this->cache = new ShippingPriceCache($this->cacheProvider, $this->keyGenerator);
    }

    /**
     * @dataProvider hasPriceDataProvider
     * @param CacheItem $cacheItem
     * @param boolean $hasPrice
     */
    public function testHasPrice($cacheItem, $hasPrice)
    {
        $context = $this->createShippingContext([]);

        $this->cacheProvider->expects(static::once())
            ->method('hasItem')
            ->with('_flat_rateprimary')
            ->willReturn($cacheItem->get());

        static::assertEquals($hasPrice, $this->cache->hasPrice($context, 'flat_rate', 'primary'));
    }


    public function hasPriceDataProvider()
    {
        return [
            [
                'isContains' => (new CacheItem())->set(true),
                'hasPrice' => true,
            ],
            [
                'isContains' => (new CacheItem())->set(false),
                'hasPrice' => false,
            ]
        ];
    }

    /**
     * @dataProvider getPriceDataProvider
     * @param boolean $isContains
     * @param Price|null $price
     */
    public function testGetPrice($isContains, Price $price = null)
    {
        $context = $this->createShippingContext([]);
        $this->cacheProvider->expects(static::any())
            ->method('hasItem')
            ->with('_flat_rateprimary')
            ->willReturn($isContains);
        $cacheItem = new CacheItem();
        $cacheItem->set($isContains ? $price : false);
        $this->cacheProvider->expects(static::any())
            ->method('getItem')
            ->with('_flat_rateprimary')
            ->willReturn($cacheItem);

        static::assertSame($price, $this->cache->getPrice($context, 'flat_rate', 'primary'));
    }

    public function getPriceDataProvider()
    {
        return [
            [
                'isContains' => true,
                'price' => Price::create(5, 'USD'),
            ],
            [
                'isContains' => false,
                'price' => null,
            ]
        ];
    }

    public function testSavePrice()
    {
        $context = $this->createShippingContext([
            ShippingContext::FIELD_SOURCE_ENTITY => new \stdClass(),
            ShippingContext::FIELD_SOURCE_ENTITY_ID => 1
        ]);

        $price = Price::create(10, 'USD');

        $this->cacheProvider->expects(static::once())
            ->method('getItem')
            ->willReturn(new CacheItem());
        $this->cacheProvider->expects(static::once())
            ->method('save');

        static::assertEquals($this->cache, $this->cache->savePrice($context, 'flat_rate', 'primary', $price));
    }

    public function testDeleteAllPrices()
    {
        $this->cacheProvider->expects(static::once())
            ->method('clear');

        $this->cache->deleteAllPrices();
    }

    /**
     * @param array $params
     *
     * @return ShippingContext
     */
    private function createShippingContext(array $params)
    {
        $actualParams = array_merge([
            ShippingContext::FIELD_LINE_ITEMS => new DoctrineShippingLineItemCollection([])
        ], $params);

        return new ShippingContext($actualParams);
    }
}
