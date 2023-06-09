<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy;

use Doctrine\Persistence\ObjectManager;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;

class ManualReplenishmentStrategy implements ReplenishmentStrategyInterface
{
    const IDENTIFIER = 'manual';
    const LABEL = 'marelloenterprise.replenishment.replenishment_strategies.manual';

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        // Exclude manual strategy from choices list
        return false;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getLabel()
    {
        return self::LABEL;
    }

    /**
     * @inheritDoc
     */
    public function getResults(ReplenishmentOrderConfig $config)
    {
        $result = [];
        foreach ($config->getManualItems() as $manualItem) {
            $result[] = [
                'origin' => $manualItem->getOrigin(),
                'destination' => $manualItem->getDestination(),
                'product' => $manualItem->getProduct(),
                'quantity' => $manualItem->getQuantity(),
                'total_quantity' => $manualItem->getAvailableQuantity(),
                'allQuantity' => $manualItem->isAllQuantity(),
            ];
        }

        return $result;
    }
}
