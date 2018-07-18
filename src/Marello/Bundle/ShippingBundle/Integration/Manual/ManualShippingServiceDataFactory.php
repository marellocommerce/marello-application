<?php

namespace Marello\Bundle\ShippingBundle\Integration\Manual;

use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ManualShippingServiceDataFactory implements ShippingServiceDataFactoryInterface
{
    /** @var ConfigManager */
    protected $configManager;

    /**
     * ManualShippingServiceDataFactory constructor.
     *
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param ShippingServiceDataProviderInterface $shippingDataProvider
     * @return array
     */
    public function createData(ShippingServiceDataProviderInterface $shippingDataProvider)
    {
        return [];
    }
}
