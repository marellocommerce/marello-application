<?php

namespace Marello\Bundle\UPSBundle\Cache\Lifetime;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;

interface LifetimeProviderInterface
{
    /**
     * @param UPSSettings $settings
     * @param int         $lifetime
     *
     * @return int
     */
    public function getLifetime(UPSSettings $settings, $lifetime);

    /**
     * @param UPSSettings $settings
     * @param string      $key
     *
     * @return string
     */
    public function generateLifetimeAwareKey(UPSSettings $settings, $key);
}
