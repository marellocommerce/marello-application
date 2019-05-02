<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSConfigFactory
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(
        ConfigManager $configManager
    ) {
        $this->configManager = $configManager;
    }

    /**
     * @return UPSConfig
     */
    public function createUPSConfig()
    {
        return new UPSConfig(
            $this->configManager,
            UPSConfigKeysProviderFactory::create()
        );
    }
}
