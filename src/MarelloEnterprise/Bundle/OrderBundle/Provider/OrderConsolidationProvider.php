<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Provider;

use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderConsolidationProvider
{
    public function __construct(
        protected ConfigManager $configManager
    ) {
    }

    /**
     * Check if consolidation feature is enabled in system settings
     * @return bool
     */
    public function isConsolidationFeatureEnabled(): bool
    {
        return (bool) $this->configManager->get('marello_enterprise_order.enable_order_consolidation');
    }

    /**
     * Check if consolidation is enabled in system scope
     * @return bool
     */
    public function isConsolidationEnabledInSystem(): bool
    {
        return $this->isConsolidationEnabled();
    }

    /**
     * Check if consolidation is enabled in saleschannel scope
     * @param SalesChannelAwareInterface $entity
     * @return bool
     */
    public function isConsolidationEnabledForSalesChannel(SalesChannelAwareInterface $entity): bool
    {
        return $this->isConsolidationEnabled($entity->getSalesChannel());
    }

    /**
     * Check if consolidation feature is enabled in system
     * @param $entity|null
     * @return bool
     */
    protected function isConsolidationEnabled($entity = null): bool
    {
        return (bool) $this->configManager
            ->get(
                'marello_enterprise_order.set_consolidation_for_scope',
                false,
                false,
                $entity
            );
    }

    /**
     * @param Order $entity
     * @return bool
     */
    public function isConsolidationEnabledForOrder(Order $entity): bool
    {
        return (
            ($this->isConsolidationEnabledInSystem() || $this->isConsolidationEnabledForSalesChannel($entity))
            && $this->isConsolidationFeatureEnabled()
        );
    }
}
