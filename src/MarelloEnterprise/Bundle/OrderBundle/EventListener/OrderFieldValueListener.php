<?php

namespace MarelloEnterprise\Bundle\OrderBundle\EventListener;

use Oro\Bundle\EntityExtendBundle\Event\ValueRenderEvent;

use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\OrderBundle\Provider\OrderConsolidationProvider;

class OrderFieldValueListener
{
    public function __construct(
        protected OrderConsolidationProvider $consolidationProvider
    ) {
    }

    /**
     * @param ValueRenderEvent $event
     * @return void
     */
    public function onBeforeValueRender(ValueRenderEvent $event): void
    {
        if ($event->getEntity() instanceof Order) {
            $fieldConfig = $event->getFieldConfigId();
            if (str_contains($fieldConfig->getFieldName(), 'consolidation_enabled')) {
                // if the feature is not configured to be enabled, do not display this field
                if (!$this->consolidationProvider->isConsolidationFeatureEnabled()) {
                    $event->setFieldVisibility(false);
                }
            }
        }
    }
}
