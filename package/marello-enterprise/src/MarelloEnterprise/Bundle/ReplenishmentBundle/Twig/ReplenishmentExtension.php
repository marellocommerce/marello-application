<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Twig;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ReplenishmentStrategiesRegistry;
use Symfony\Component\Translation\TranslatorInterface;

class ReplenishmentExtension extends \Twig_Extension
{
    const NAME = 'marello_replenishment';
    
    /**
     * @var ReplenishmentStrategiesRegistry
     */
    protected $replenishmentStrategiesRegistry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ReplenishmentStrategiesRegistry $replenishmentStrategiesRegistry
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ReplenishmentStrategiesRegistry $replenishmentStrategiesRegistry,
        TranslatorInterface $translator
    ) {
        $this->replenishmentStrategiesRegistry = $replenishmentStrategiesRegistry;
        $this->translator = $translator;
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
                'marello_replenishment_get_strategy_label',
                [$this, 'getStrategyLabel']
            )
        ];
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function getStrategyLabel($identifier)
    {
        $strategy = $this->replenishmentStrategiesRegistry->getStrategy($identifier);
        if ($strategy) {
            return $this->translator->trans($strategy->getLabel());
        }
        
        return $identifier;
    }
}
