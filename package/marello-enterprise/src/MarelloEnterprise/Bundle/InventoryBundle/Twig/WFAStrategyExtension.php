<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Twig;

use MarelloEnterprise\Bundle\InventoryBundle\Formatter\WFAStrategyLabelFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WFAStrategyExtension extends AbstractExtension
{
    const NAME = 'marello_wfa_strategy';

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
        return static::NAME;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_inventory_wfa_strategy_label',
                [$this->wfaStrategyLabelFormatter, 'formatLabel']
            )
        ];
    }
}
