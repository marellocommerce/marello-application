<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\ActionInterface;

use Marello\Bundle\CoreBundle\Workflow\Action\WorkflowTransitAction;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class TransitCompleteAction extends WorkflowTransitAction
{
    /** @var PropertyPathInterface $entity */
    protected $entity;

    /**
     * {@inheritdoc}
     * @param mixed $context
     * @throws \Exception
     */
    protected function executeAction($context)
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->contextAccessor->getValue($context, $this->entity);

        if (!$purchaseOrder) {
            throw new \Exception('Invalid configuration of workflow action, expected entity, none given.');
        }

        if (!$purchaseOrder instanceof PurchaseOrder) {
            return;
        }

        $items = $purchaseOrder->getItems();
        $completedItems = 0;
        /** @var PurchaseOrderItem $item */
        foreach ($items as $item) {
            if ($item->getOrderedAmount() === $item->getReceivedAmount()) {
                $completedItems++;
            }
        }

        if ($items->count() === $completedItems) {
            parent::executeAction($context);
        }
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        if (!array_key_exists('entity', $options)) {
            throw new InvalidParameterException('Parameter "entity" is required.');
        } elseif (!$options['entity'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Entity must be valid property definition.');
        } else {
            $this->entity = $this->getOption($options, 'entity');
        }

        parent::initialize($options);
    }
}
