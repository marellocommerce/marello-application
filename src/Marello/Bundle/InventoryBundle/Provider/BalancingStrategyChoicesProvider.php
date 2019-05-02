<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategiesRegistry;
use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategyInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BalancingStrategyChoicesProvider
{
    /**
     * @var BalancerStrategiesRegistry
     */
    protected $strategiesRegistry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param BalancerStrategiesRegistry $strategiesRegistry
     * @param TranslatorInterface    $translator
     */
    public function __construct(BalancerStrategiesRegistry $strategiesRegistry, TranslatorInterface $translator)
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
            function (array $result, BalancerStrategyInterface $strategy) {
                if ($strategy->isEnabled()) {
                    $result[$strategy->getIdentifier()] = $this->translator->trans($strategy->getLabel());
                }

                return $result;
            },
            []
        );
    }
}
