<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\Action\Model\ContextAccessor;

use Marello\Bundle\PurchaseOrderBundle\Processor\NoteActivityProcessor;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class ReceivePurchaseOrderAction extends AbstractAction
{
    const LAST_PARTIALLY_RECEIVED_QTY = 'last_partially_received_qty';

    /** @var array */
    protected $options;

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var NoteActivityProcessor $noteActivityProcessor */
    protected $noteActivityProcessor;

    /** @var PropertyPathInterface $entity */
    protected $entity;

    /** @var PropertyPathInterface|bool $isPartial */
    protected $isPartial;

    /**
     * ReceivePurchaseOrderAction constructor.
     * @param ContextAccessor $contextAccessor
     * @param ObjectManager $manager
     * @param NoteActivityProcessor $noteActivityProcessor
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        ObjectManager $manager,
        NoteActivityProcessor $noteActivityProcessor
    ) {
        parent::__construct($contextAccessor);

        $this->manager = $manager;
        $this->noteActivityProcessor = $noteActivityProcessor;
    }

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
            throw new \Exception('Invalid configuration of workflow action, expected entity, null given');
        }

        if (!$purchaseOrder instanceof PurchaseOrder) {
            return;
        }

        $isPartial = $this->contextAccessor->getValue($context, $this->isPartial);
        $items = $purchaseOrder->getItems();
        $updatedItems = [];
        /** @var PurchaseOrderItem $item */
        foreach ($items as $item) {
            $inventoryUpdateQty = null;
            /** @var InventoryItem $inventoryItem */
            if ($isPartial) {
                $data = (array)$item->getData();
                if (array_key_exists(self::LAST_PARTIALLY_RECEIVED_QTY, $data)) {
                    $inventoryUpdateQty = $data[self::LAST_PARTIALLY_RECEIVED_QTY];
                    $item->setData(null);
                }
            } else {
                if (!$this->isItemFullyReceived($item)) {
                    $item->setReceivedAmount($item->getOrderedAmount());
                    $inventoryUpdateQty = $item->getReceivedAmount();
                }
            }

            if ($this->isItemFullyReceived($item)) {
                $item->setStatus('complete');
            }

            if ($inventoryUpdateQty) {
                $this->handleInventoryUpdate($item, $inventoryUpdateQty);
                $updatedItems[] = ['qty' => $inventoryUpdateQty, 'item' => $item];
            }
        }

        if (!empty($updatedItems)) {
            $this->noteActivityProcessor->addNote($purchaseOrder, $updatedItems);
        }

        $this->manager->flush();
    }

    /**
     * handle the inventory update for items which have been received
     * @param $item
     * @param $inventoryUpdateQty
     */
    private function handleInventoryUpdate($item, $inventoryUpdateQty)
    {
        $inventoryItem = $item->getProduct()->getInventoryItems()->first();
        $inventoryItem->adjustStockLevels('purchase_order', $inventoryUpdateQty);
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

    /**
     * Check if item is fully received
     * @param $item
     * @return bool
     */
    private function isItemFullyReceived($item)
    {
        return ($item->getOrderedAmount() === $item->getReceivedAmount());
    }
}
