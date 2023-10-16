<?php

namespace Marello\Bundle\ShippingBundle\Provider\Cache;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ShippingBundle\Context\ShippingContextCacheKeyGenerator;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Psr\Cache\CacheItemPoolInterface;

class ShippingPriceCache
{
    /**
     * 1 hour, 60 * 60
     */
    const CACHE_LIFETIME = 3600;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var ShippingContextCacheKeyGenerator
     */
    protected $cacheKeyGenerator;

    /**
     * @param CacheItemPoolInterface $cacheProvider
     * @param ShippingContextCacheKeyGenerator $cacheKeyGenerator
     */
    public function __construct(
        CacheItemPoolInterface $cacheProvider,
        ShippingContextCacheKeyGenerator $cacheKeyGenerator
    ) {
        $this->cache = $cacheProvider;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
    }

    /**
     * @param ShippingContextInterface $context
     * @param string $methodId
     * @param string $typeId
     * @return Price|null
     */
    public function getPrice(ShippingContextInterface $context, $methodId, $typeId)
    {
        $key = $this->generateKey($context, $methodId, $typeId);
        if (!$this->cache->hasItem($key)) {
            return null;
        }
        return $this->cache->getItem($key)->get();
    }

    /**
     * @param ShippingContextInterface $context
     * @param string $methodId
     * @param string $typeId
     * @return bool
     */
    public function hasPrice(ShippingContextInterface $context, $methodId, $typeId)
    {
        return $this->cache->hasItem($this->generateKey($context, $methodId, $typeId));
    }

    /**
     * @param ShippingContextInterface $context
     * @param string $methodId
     * @param string $typeId
     * @param Price $price
     * @return $this
     */
    public function savePrice(ShippingContextInterface $context, $methodId, $typeId, Price $price)
    {
        $key = $this->generateKey($context, $methodId, $typeId);
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($price);
        $cacheItem->expiresAfter(static::CACHE_LIFETIME);
        $this->cache->save($cacheItem);

        return $this;
    }

    /**
     * @param ShippingContextInterface $context
     * @param string $methodId
     * @param string $typeId
     * @return string
     */
    protected function generateKey(ShippingContextInterface $context, $methodId, $typeId)
    {
        return $this->cacheKeyGenerator->generateKey($context).$methodId.$typeId;
    }
    
    public function deleteAllPrices()
    {
        $this->cache->clear();
    }
}
