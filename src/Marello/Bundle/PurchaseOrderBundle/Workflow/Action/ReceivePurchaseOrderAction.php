<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\Action\Model\ContextAccessor;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class ReceivePurchaseOrderAction extends AbstractAction
{
    /** @var array */
    protected $options;

    /** @var Registry */
    protected $doctrine;

    /** @var PropertyPathInterface */
    protected $entity;

    /** @var PropertyPathInterface|bool */
    protected $isPartial;

    /**
     * CreateRefundAction constructor.
     *
     * @param ContextAccessor $contextAccessor
     * @param Registry        $doctrine
     */
    public function __construct(ContextAccessor $contextAccessor, Registry $doctrine)
    {
        parent::__construct($contextAccessor);

        $this->doctrine = $doctrine;
    }

    /**
     * @param mixed $context
     * @throws \Exception
     */
    protected function executeAction($context)
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->contextAccessor->getValue($context, $this->entity);
        if (!$purchaseOrder) {
            throw new \Exception('Invalid configuration of workflow action, expected entity, null given');
        }

        if (!$purchaseOrder instanceof PurchaseOrder) {
            return;
        }

        $isPartial = $this->contextAccessor->getValue($context, $this->isPartial);
        $items = $purchaseOrder->getItems();
        foreach ($items as $item) {
            /** @var InventoryItem $inventoryItem */
            if ($isPartial) {
                file_put_contents(
                    '/Users/hotlander/Development/marello-application-dev/app/logs/receiving.log',
                    $item->getOrderedAmount() . "\r\n",
                    FILE_APPEND
                );

                file_put_contents(
                    '/Users/hotlander/Development/marello-application-dev/app/logs/receiving.log',
                    $item->getReceivedAmount() . "\r\n",
                    FILE_APPEND
                );
            } else {
                $this->handleFullyReceived($item);
            }

        }
        $this->doctrine->getManager()->flush();
    }

    private function handleFullyReceived($item)
    {
        $inventoryItem = $item->getProduct()->getInventoryItems()->first();
        $inventoryItem->adjustStockLevels('purchase_order', $item->getOrderedAmount());
        $item->setReceivedAmount($item->getOrderedAmount());
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
        if (!array_key_exists('entity', $options) && !$options['entity'] instanceof PropertyPathInterface) {
            throw new InvalidParameterException('Parameter "entity" is required.');
        } else {
            $this->entity = $this->getOption($options, 'entity');
        }

        if (array_key_exists('is_partial', $options)) {
            $this->isPartial = $this->getOption($options, 'is_partial');
        }
    }
}
