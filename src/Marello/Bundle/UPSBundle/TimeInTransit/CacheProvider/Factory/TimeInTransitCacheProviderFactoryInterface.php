<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\Factory;

use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\TimeInTransitCacheProviderInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

interface TimeInTransitCacheProviderFactoryInterface
{
    /**
     * @param UPSSettings $settings
     *
     * @return TimeInTransitCacheProviderInterface
     */
    public function createCacheProviderForTransport(UPSSettings $settings);
}
