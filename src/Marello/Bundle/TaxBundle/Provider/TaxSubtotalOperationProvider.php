<?php

namespace Marello\Bundle\TaxBundle\Provider;

use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class TaxSubtotalOperationProvider
{
    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(
        ConfigManager $configManager
    ) {
        $this->configManager = $configManager;
    }
    
    public function getSubtotalOperation()
    {
        if ($this->isPricesIncludeTax()) {
            return Subtotal::OPERATION_SUBTRACTION;
        }

        return Subtotal::OPERATION_IGNORE;
    }
    
    protected function isPricesIncludeTax()
    {
        return $this->configManager->get(Configuration::VAT_SYSTEM_CONFIG_PATH);
    }
}
