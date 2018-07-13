<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\Factory;

use Doctrine\Common\Cache\CacheProvider;
use Marello\Bundle\UPSBundle\Cache\Lifetime\LifetimeProviderInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\TimeInTransitCacheProvider;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\TimeInTransitCacheProviderInterface;

class TimeInTransitCacheProviderFactory implements TimeInTransitCacheProviderFactoryInterface
{
    /**
     * @internal
     */
    const CACHE_NAMESPACE_PREFIX = 'marello_ups_time_in_transit';

    /**
     * @var CacheProvider[]
     */
    private $cacheProviderInstances = [];

    /**
     * @var CacheProvider
     */
    private $cacheProviderPrototype;

    /**
     * @var LifetimeProviderInterface
     */
    private $lifetimeProvider;

    /**
     * @param CacheProvider             $cacheProvider
     * @param LifetimeProviderInterface $lifetimeProvider
     */
    public function __construct(CacheProvider $cacheProvider, LifetimeProviderInterface $lifetimeProvider)
    {
        $this->cacheProviderPrototype = $cacheProvider;
        $this->lifetimeProvider = $lifetimeProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function createCacheProviderForTransport(UPSSettings $settings)
    {
        $settingsId = $settings->getId();

        if (!array_key_exists($settingsId, $this->cacheProviderInstances)) {
            $this->cacheProviderInstances[$settingsId] = $this->createCacheProvider($settings);
        }

        return $this->cacheProviderInstances[$settingsId];
    }

    /**
     * @param UPSSettings $settings
     *
     * @return TimeInTransitCacheProviderInterface
     */
    private function createCacheProvider(UPSSettings $settings)
    {
        $cacheProvider = clone $this->cacheProviderPrototype;
        $cacheProvider->setNamespace(sprintf('%s_%d', self::CACHE_NAMESPACE_PREFIX, $settings->getId()));

        return new TimeInTransitCacheProvider($settings, $cacheProvider, $this->lifetimeProvider);
    }
}
