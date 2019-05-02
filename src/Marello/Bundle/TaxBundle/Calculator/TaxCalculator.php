<?php

namespace Marello\Bundle\TaxBundle\Calculator;

use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class TaxCalculator implements TaxCalculatorInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var TaxCalculatorInterface
     */
    protected $includedTaxCalculator;

    /**
     * @var TaxCalculatorInterface
     */
    protected $excludedTaxCalculator;

    /**
     * @param ConfigManager $configManager
     * @param TaxCalculatorInterface $includedTaxCalculator
     * @param TaxCalculatorInterface $excludedTaxCalculator
     */
    public function __construct(
        ConfigManager $configManager,
        TaxCalculatorInterface $includedTaxCalculator,
        TaxCalculatorInterface $excludedTaxCalculator
    ) {
        $this->configManager = $configManager;
        $this->includedTaxCalculator = $includedTaxCalculator;
        $this->excludedTaxCalculator = $excludedTaxCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($amount, $taxRate)
    {
        if ($this->isPricesIncludeTax()) {
            return $this->includedTaxCalculator->calculate($amount, $taxRate);
        }

        return $this->excludedTaxCalculator->calculate($amount, $taxRate);
    }
    
    protected function isPricesIncludeTax()
    {
        return $this->configManager->get(Configuration::VAT_SYSTEM_CONFIG_PATH);
    }
}
