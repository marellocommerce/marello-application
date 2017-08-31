<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Formatter;

use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use Symfony\Component\Translation\TranslatorInterface;

class WFAStrategyLabelFormatter
{
    /**
     * @var WFAStrategiesRegistry
     */
    protected $strategiesRegistry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param TranslatorInterface    $translator
     */
    public function __construct(WFAStrategiesRegistry $strategiesRegistry, TranslatorInterface $translator)
    {
        $this->strategiesRegistry = $strategiesRegistry;
        $this->translator = $translator;
    }

    /**
     * @param string $identifier
     * @return null|string
     */
    public function formatLabel($identifier)
    {
        if ($strategy = $this->strategiesRegistry->getStrategy($identifier)) {
            return $this->translator->trans($strategy->getLabel());
        }
        
        return null;
    }
}
