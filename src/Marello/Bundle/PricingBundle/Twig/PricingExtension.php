<?php

namespace Marello\Bundle\PricingBundle\Twig;

use Marello\Bundle\PricingBundle\Formatter\LabelVATAwareFormatter;
use Marello\Bundle\PricingBundle\Provider\CurrencyProvider;

class PricingExtension extends \Twig_Extension
{
    const NAME = 'marello_pricing';

    /**
     * @var CurrencyProvider
     */
    protected $currencyProvider;

    /**
     * @var LabelVATAwareFormatter
     */
    protected $vatLabelFormatter;

    /**
     * @param CurrencyProvider $currencyProvider
     * @param LabelVATAwareFormatter $vatLabelFormatter
     */
    public function __construct(CurrencyProvider $currencyProvider, LabelVATAwareFormatter $vatLabelFormatter)
    {
        $this->currencyProvider = $currencyProvider;
        $this->vatLabelFormatter = $vatLabelFormatter;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'marello_pricing_get_currency_data',
                [$this, 'getCurrencyData']
            ),
            new \Twig_SimpleFunction(
                'marello_pricing_vat_aware_label',
                [$this->vatLabelFormatter, 'getFormattedLabel']
            ),
        ];
    }

    /**
     * @param array $data
     *
     * @return string|null
     */
    public function getCurrencyData(array $data)
    {
        if (!empty($data['currencyCode']) && !empty($data['currencySymbol'])) {
            return $this->formatData($data['currencyCode'], $data['currencySymbol']);
        }

        if (!empty($data['currencyCode']) && empty($data['currencySymbol'])) {
            $currencySymbol = $this->currencyProvider->getCurrencySymbol($data['currencyCode']);

            return $this->formatData($data['currencyCode'], $currencySymbol);
        }

        if (!empty($data['salesChannel'])) {
            $currencyData = $this->currencyProvider->getCurrencyDataByChannel($data['salesChannel']);
            $key          = sprintf('currency-%s', $data['salesChannel']);

            return $this->formatData($currencyData[$key]['currencyCode'], $currencyData[$key]['currencySymbol']);
        }
        
        return null;
    }

    /**
     * Format currency data
     *
     * @param $currencyCode
     * @param $currencySymbol
     *
     * @return string
     */
    private function formatData($currencyCode, $currencySymbol)
    {
        return sprintf('%s (%s)', $currencyCode, $currencySymbol);
    }
}
