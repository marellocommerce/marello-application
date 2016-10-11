<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class ReceivePurchaseOrderAction extends AbstractAction
{
    /** @var array */
    protected $options;

    /** @var Registry */
    protected $doctrine;

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
     */
    protected function executeAction($context)
    {
        /** @var PurchaseOrder $po */
        $po = $context->getEntity();

        $items = $po->getItems();

        foreach ($items as $item) {
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $item->getProduct()->getInventoryItems()->first();

            $inventoryItem->adjustStockLevels('purchase_order', $item->getOrderedAmount());
            $item->setReceivedAmount($item->getOrderedAmount());
        }

        $this->doctrine->getManager()->flush();
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
        $this->options = $options;

        return $this;
    }
}
