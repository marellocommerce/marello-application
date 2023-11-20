<?php

namespace Marello\Bundle\UPSBundle\Cache;

use Oro\Bundle\CacheBundle\Provider\FilesystemCache;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\UPSBundle\Cache\Lifetime\LifetimeProviderInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;
use Psr\Cache\CacheItemPoolInterface;

class ShippingPriceCache
{
    /**
     * 24 hours, 60 * 60 * 24
     */
    const LIFETIME = 86400;

    const NAME_SPACE = 'marello_ups_shipping_price';

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var LifetimeProviderInterface
     */
    protected $lifetimeProvider;

    /**
     * @param CacheItemPoolInterface    $cache
     * @param LifetimeProviderInterface $lifetimeProvider
     */
    public function __construct(CacheItemPoolInterface $cache, LifetimeProviderInterface $lifetimeProvider)
    {
        $this->cache = $cache;
        $this->lifetimeProvider = $lifetimeProvider;
    }

    /**
     * @param ShippingPriceCacheKey $key
     *
     * @return bool
     */
    public function containsPrice(ShippingPriceCacheKey $key)
    {
        $this->setNamespace($key->getTransport()->getId());

        return $this->containsPriceByStringKey($this->generateStringKey($key));
    }

    /**
     * @param string $stringKey
     *
     * @return bool
     */
    protected function containsPriceByStringKey($stringKey)
    {
        return $this->cache->hasItem($stringKey);
    }

    /**
     * @param ShippingPriceCacheKey $key
     *
     * @return bool|Price
     */
    public function fetchPrice(ShippingPriceCacheKey $key)
    {
        $this->setNamespace($key->getTransport()->getId());

        $stringKey = $this->generateStringKey($key);
        if (!$this->containsPriceByStringKey($stringKey)) {
            return false;
        }

        return $this->cache->getItem($stringKey)->get();
    }

    /**
     * @param ShippingPriceCacheKey $key
     * @param Price                 $price
     *
     * @return $this
     */
    public function savePrice(ShippingPriceCacheKey $key, Price $price)
    {
        $this->setNamespace($key->getTransport()->getId());

        $lifetime = $this->lifetimeProvider->getLifetime($key->getTransport(), static::LIFETIME);

        $cacheItem = $this->cache->getItem($this->generateStringKey($key));
        $cacheItem->set($price);
        $cacheItem->expiresAfter($lifetime);
        $this->cache->save($cacheItem);

        return $this;
    }

    /**
     * @param integer $transportId
     */
    public function deleteAll($transportId)
    {
        $this->setNamespace($transportId);
        $this->cache->clear();
    }

    /**
     * @param UPSSettings $transport
     * @param PriceRequest $priceRequest
     * @param string       $methodId
     * @param string       $typeId
     *
     * @return ShippingPriceCacheKey
     */
    public function createKey(
        UPSSettings $transport,
        PriceRequest $priceRequest,
        $methodId,
        $typeId
    ) {
        return (new ShippingPriceCacheKey())->setTransport($transport)->setPriceRequest($priceRequest)
            ->setMethodId($methodId)->setTypeId($typeId);
    }

    /**
     * @param ShippingPriceCacheKey $key
     *
     * @return string
     */
    protected function generateStringKey(ShippingPriceCacheKey $key)
    {
        return $this->lifetimeProvider->generateLifetimeAwareKey($key->getTransport(), $key->generateKey());
    }

    /**
     * @param integer $id
     */
    protected function setNamespace($id)
    {
        if ($this->cache instanceof FilesystemCache) {
            $this->cache->setNamespace(self::NAME_SPACE.'_'.$id);
        }
    }
}
