<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Provider;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ReplenishmentStrategiesRegistry;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ReplenishmentStrategyInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplenishmentStrategyChoicesProvider
{
    /**
     * @var ReplenishmentStrategiesRegistry
     */
    protected $strategiesRegistry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ReplenishmentStrategiesRegistry $strategiesRegistry
     * @param TranslatorInterface    $translator
     */
    public function __construct(ReplenishmentStrategiesRegistry $strategiesRegistry, TranslatorInterface $translator)
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
            function (array $result, ReplenishmentStrategyInterface $strategy) {
                if ($strategy->isEnabled()) {
                    $result[$strategy->getIdentifier()] = $this->translator->trans($strategy->getLabel());
                }

                return $result;
            },
            []
        );
    }
}
