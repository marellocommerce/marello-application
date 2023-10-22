<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\CurrencyBundle\Entity\Price;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCacheKey;
use Marello\Bundle\UPSBundle\Cache\Lifetime\LifetimeProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

class ShippingPriceCacheTest extends TestCase
{
    /**
     * @internal
     */
    const CACHE_KEY = 'cache_key';

    /**
     * @internal
     */
    const SETTINGS_ID = 7;

    /**
     * @var ShippingPriceCache
     */
    private $cache;

    /**
     * @var CacheItemPoolInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheProvider;

    /**
     * @var LifetimeProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lifetimeProvider;

    /**
     * @var UPSSettings|\PHPUnit\Framework\MockObject\MockObject
     */
    private $settings;

    /**
     * @var ShippingPriceCacheKey|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheKey;

    public function setUp(): void
    {
        $this->cacheProvider = $this->createMock(CacheItemPoolInterface::class);
        $this->lifetimeProvider = $this->createMock(LifetimeProviderInterface::class);

        $this->settings = $this->createMock(UPSSettings::class);

        $this->settings
            ->method('getId')
            ->willReturn(self::SETTINGS_ID);

        $this->cacheKey = $this->getCacheKeyMock($this->settings, self::CACHE_KEY);

        $this->lifetimeProvider->method('generateLifetimeAwareKey')
            ->with($this->settings, self::CACHE_KEY)
            ->willReturn(self::CACHE_KEY);

        $this->cache = new ShippingPriceCache($this->cacheProvider, $this->lifetimeProvider);
    }

    public function testFetchPrice()
    {
        $this->cacheProvider->expects(static::once())
            ->method('hasItem')
            ->with(self::CACHE_KEY)
            ->willReturn(true);

        $price = Price::create(10, 'USD');
        $cacheItem = new CacheItem();
        $cacheItem->set($price);

        $this->cacheProvider->expects(static::once())
            ->method('getItem')
            ->with(self::CACHE_KEY)
            ->willReturn($cacheItem);

        static::assertSame($price, $this->cache->fetchPrice($this->cacheKey));
    }

    public function testFetchPriceFalse()
    {
        $this->cacheProvider->expects(static::once())
            ->method('hasItem')
            ->with(self::CACHE_KEY)
            ->willReturn(false);

        $this->cacheProvider->expects(static::never())
            ->method('getItem');

        static::assertFalse($this->cache->fetchPrice($this->cacheKey));
    }

    public function testContainsPrice()
    {
        $this->cacheProvider->expects(static::once())
            ->method('hasItem')
            ->with(self::CACHE_KEY)
            ->willReturn(true);

        static::assertTrue($this->cache->containsPrice($this->cacheKey));
    }

    public function testContainsPriceFalse()
    {
        $this->cacheProvider->expects(static::once())
            ->method('hasItem')
            ->with(self::CACHE_KEY)
            ->willReturn(false);

        static::assertFalse($this->cache->containsPrice($this->cacheKey));
    }

    public function testSavePrice()
    {
        $lifetime = 100;

        $this->lifetimeProvider->method('getLifetime')
            ->with($this->settings, 86400)
            ->willReturn($lifetime);

        $price = Price::create(10, 'USD');
        $this->cacheProvider->expects(static::once())
            ->method('getItem')
            ->willReturn(new CacheItem());
        $this->cacheProvider->expects(static::once())
            ->method('save');

        static::assertEquals($this->cache, $this->cache->savePrice($this->cacheKey, $price));
    }

    /**
     * @param UPSSettings $settings
     * @param string      $stringKey
     *
     * @return ShippingPriceCacheKey|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getCacheKeyMock(UPSSettings $settings, string $stringKey): ShippingPriceCacheKey
    {
        $mock = $this->createMock(ShippingPriceCacheKey::class);

        $mock->method('getTransport')
            ->willReturn($settings);

        $mock->method('generateKey')
            ->willReturn($stringKey);

        return $mock;
    }
}
