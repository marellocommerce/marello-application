<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Twig;

use MarelloEnterprise\Bundle\InventoryBundle\Formatter\WFAStrategyLabelFormatter;

class WFAStrategyExtension extends \Twig_Extension
{
    const SHIPPING_METHOD_EXTENSION_NAME = 'marello_wfa_strategy';

    /**
     * @var WFAStrategyLabelFormatter
     */
    protected $wfaStrategyLabelFormatter;

    /**
     * @param WFAStrategyLabelFormatter $wfaStrategyLabelFormatter
     */
    public function __construct(WFAStrategyLabelFormatter $wfaStrategyLabelFormatter)
    {
        $this->wfaStrategyLabelFormatter = $wfaStrategyLabelFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::SHIPPING_METHOD_EXTENSION_NAME;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'marello_inventory_wfa_strategy_label',
                [$this->wfaStrategyLabelFormatter, 'formatLabel']
            )
        ];
    }
}
