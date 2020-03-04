<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;

interface ReplenishmentStrategyInterface
{
    /**
     * @return string|int
     */
    public function getIdentifier();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param ReplenishmentOrderConfig $config
     * @return array
     */
    public function getResults(ReplenishmentOrderConfig $config);
}
