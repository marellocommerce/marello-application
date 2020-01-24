<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;
use Symfony\Component\Translation\TranslatorInterface;

class WFAStrategyChoicesProvider
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
     * {@inheritdoc}
     */
    public function getChoices()
    {
        return array_reduce(
            $this->strategiesRegistry->getStrategies(),
            function (array $result, WFAStrategyInterface $strategy) {
                if ($strategy->isEnabled()) {
                    $result[$strategy->getIdentifier()] = $this->translator->trans($strategy->getLabel());
                }

                return $result;
            },
            []
        );
    }
}
