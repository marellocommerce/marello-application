<?php

namespace Marello\Bundle\PricingBundle\EventListener\Datagrid;

use Marello\Bundle\PricingBundle\Formatter\LabelVATAwareFormatter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class PriceVATLabelAwareGridListener
{
    /**
     * @var LabelVATAwareFormatter
     */
    protected $vatLabelFormatter;

    /**
     * @param LabelVATAwareFormatter $vatLabelFormatter
     */
    public function __construct(LabelVATAwareFormatter $vatLabelFormatter)
    {
        $this->vatLabelFormatter = $vatLabelFormatter;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $columnConfig = $config->offsetGetByPath('[columns][price]');
        if ($columnConfig) {
            $columnConfig['label'] = $this->vatLabelFormatter->getFormattedLabel($columnConfig['label']);
            $config->offsetSetByPath('[columns][price]', $columnConfig);
        }
    }
}
