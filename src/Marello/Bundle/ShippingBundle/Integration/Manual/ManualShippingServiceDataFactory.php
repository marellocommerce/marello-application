<?php

namespace Marello\Bundle\ShippingBundle\Integration\Manual;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
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
