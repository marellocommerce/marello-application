<?php

namespace Marello\Bundle\POSBundle\EventListener\Workflow;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\Action\Event\ExtendableActionEvent;

use Marello\Bundle\OrderBundle\Entity\Order;

class PosOrderWorkflowListener
{
    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        protected DoctrineHelper $doctrineHelper
    ) {
    }

    /**
     * @param ExtendableActionEvent $event
     */
    public function onOrderPending(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectOrderContext($event->getContext())) {
            return;
        }
        /** @var Order $entity */
        $entity = $event->getContext()->getData()->get('order');
        $entityData = $entity->getData();
        $entityData = array_shift($entityData);
        if (!empty($entityData)) {
            if (isset($entityData['paymentDetails'])) {
                $event->getContext()->getData()->set('payment_details', $entityData['paymentDetails']);
            }

            if (isset($entityData['paymentReference'])) {
                $event->getContext()->getData()->set('payment_reference', $entityData['paymentReference']);
            }

            if (isset($entityData['amount'])) {
                $event->getContext()->getData()->set(
                    'total_paid',
                    Price::create($entityData['amount'], $entity->getCurrency())
                );
            }
        }
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectOrderContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('order')
            && $context->getData()->get('order') instanceof Order
        );
    }
}
