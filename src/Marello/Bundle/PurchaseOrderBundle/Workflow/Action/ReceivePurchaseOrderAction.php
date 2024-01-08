<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitTopic;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\PurchaseOrderBundle\Processor\NoteActivityProcessor;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener;

class ReceivePurchaseOrderAction extends AbstractAction
{
    use JobIdGenerationTrait;

    const LAST_PARTIALLY_RECEIVED_QTY = 'last_partially_received_qty';
    const ALLOCATION_WORKFLOW_RESOLVED = 'resolved';

    /** @var array */
    protected $options;

    /** @var PropertyPathInterface $entity */
    protected $entity;

    /** @var PropertyPathInterface|bool $isPartial */
    protected $isPartial;

    /** @var PropertyPathInterface|bool $pickupLocation */
    protected $pickupLocation;

    /**
     * ReceivePurchaseOrderAction constructor.
     * @param ContextAccessor $contextAccessor
     * @param DoctrineHelper $doctrineHelper
     * @param NoteActivityProcessor $noteActivityProcessor
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        protected DoctrineHelper $doctrineHelper,
        protected NoteActivityProcessor $noteActivityProcessor,
        protected InventoryAllocationProvider $allocationProvider,
        protected MessageProducerInterface $messageProducer
    ) {
        parent::__construct($contextAccessor);
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
            throw new \Exception('Invalid configuration of workflow action, expected entity, none given.');
        }

        if (!$purchaseOrder instanceof PurchaseOrder) {
            return;
        }

        $isPartial = $this->contextAccessor->getValue($context, $this->isPartial);
        $pickupLocation = $this->contextAccessor->getValue($context, $this->pickupLocation);
        $items = $purchaseOrder->getItems();
        $updatedItems = [];
        $fullyReceivedItems = 0;
        /** @var PurchaseOrderItem $item */
        foreach ($items as $item) {
            $isFullyReceived = $this->isItemFullyReceived($item);
            $inventoryUpdateQty = null;
            $data = (array)$item->getData();
            /** @var InventoryItem $inventoryItem */
            if ($isPartial) {
                if (array_key_exists(self::LAST_PARTIALLY_RECEIVED_QTY, $data)) {
                    $inventoryUpdateQty = $data[self::LAST_PARTIALLY_RECEIVED_QTY];
                    unset($data[self::LAST_PARTIALLY_RECEIVED_QTY]);
                    $item->setData($data);
                }
            } else {
                if (!$isFullyReceived) {
                    $lastReceived = $item->getReceivedAmount();
                    $item->setReceivedAmount($item->getOrderedAmount());
                    $inventoryUpdateQty = $item->getReceivedAmount() - $lastReceived;
                }
            }

            if ($inventoryUpdateQty) {
                $this->handleInventoryUpdate($item, $inventoryUpdateQty, $purchaseOrder);
                $updatedItems[] = ['qty' => $inventoryUpdateQty, 'item' => $item];

                // both cases are independent of the qty that have been received
                if ($product = $item->getProduct()) {
                    $inventoryItem = $product->getInventoryItem();
                    // back-order remove date for item
                    $inventoryItem->setBackOrdersDatetime(null);
                    // pre-order remove date and set pre-order to false for inventory item
                    $inventoryItem->setCanPreorder(false);
                    $inventoryItem->setPreOrdersDatetime(null);
                }
            }

            if ($isFullyReceived) {
                $data = $item->getData();
                $orderOnDemandKey = PurchaseOrderOnOrderOnDemandCreationListener::ORDER_ON_DEMAND;
                if (isset($data[$orderOnDemandKey]) && $item->getStatus() !== PurchaseOrderItem::STATUS_COMPLETE) {
                    $repo = $this->doctrineHelper->getEntityRepositoryForClass(Allocation::class);
                    /** @var Allocation $allocation */
                    $allocation = $repo->find($data[$orderOnDemandKey]['allocation']);
                    // add check to see if the allocation is not closed yet...
                    if ($allocation->getStatus()->getId() === AllocationStateStatusInterface::ALLOCATION_STATE_CLOSED) {
                        // the original allocation is closed, wether it was random or because another purchase order
                        // triggered the closing of said allocation. We need to find the new allocation to prevent issues
                        // with double allocation of the items.
                        $allocItemRepo = $this->doctrineHelper->getEntityRepositoryForClass(AllocationItem::class);
                        /** @var AllocationItem|null $allocationItem */
                        $allocationItem = $allocItemRepo
                            ->findOneBy(
                                [
                                    'warehouse' => null,
                                    'orderItem' => $data[$orderOnDemandKey]['orderItem']
                                ],
                                [
                                    'id' => 'DESC'
                                ]
                            );
                        if ($allocationItem) {
                            $allocation = $allocationItem->getAllocation();
                        }
                    }
                    // send re-allocate message for the allocation
                    $this->allocationProvider->allocateOrderToWarehouses($allocation->getOrder(), $allocation);
                    $allocation->setState(
                        $this->getEnumValue(
                            AllocationStateStatusInterface::ALLOCATION_STATE_ENUM_CODE,
                            AllocationStateStatusInterface::ALLOCATION_STATE_CLOSED
                        )
                    );
                    $allocation->setStatus(
                        $this->getEnumValue(
                            AllocationStateStatusInterface::ALLOCATION_STATUS_ENUM_CODE,
                            AllocationStateStatusInterface::ALLOCATION_STATUS_CLOSED
                        )
                    );
                    $this->doctrineHelper->getEntityManagerForClass(Allocation::class)->persist($allocation);
                    $this->updateAllocationWorkflow($allocation);
                }
                $item->setStatus(PurchaseOrderItem::STATUS_COMPLETE);
            }

            if ($pickupLocation) {
                $this->setPickupLocation($item, $pickupLocation);
            }
        }

        if (!empty($updatedItems)) {
            $this->noteActivityProcessor->addNote($purchaseOrder, $updatedItems);
        }

        $this->doctrineHelper->getManager()->flush();
    }

    /**
     * handle the inventory update for items which have been received
     * @param PurchaseOrderItem $item
     * @param $inventoryUpdateQty
     * @param PurchaseOrder $purchaseOrder
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $purchaseOrder)
    {
        $data = $item->getData();
        $orderOnDemandKey = PurchaseOrderOnOrderOnDemandCreationListener::ORDER_ON_DEMAND;
        if (isset($data[$orderOnDemandKey])) {
            $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryBatch::class);
            /** @var InventoryBatch $batch */
            $batch = $repo->findOneBy(['orderOnDemandRef' => $data[$orderOnDemandKey]['orderItem']]);
            if ($batch) {
                $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
                    $batch->getInventoryLevel(),
                    $batch->getInventoryLevel()->getInventoryItem(),
                    [['batch' => $batch, 'qty' => $inventoryUpdateQty]],
                    $inventoryUpdateQty,
                    null,
                    'purchase_order',
                    $purchaseOrder
                );
            }
        } else {
            $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
                $item,
                null,
                $inventoryUpdateQty,
                null,
                'purchase_order',
                $purchaseOrder
            );
        }

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }

    protected function setPickupLocation(PurchaseOrderItem $item, string $pickupLocation): void
    {
        $inventoryItem = $item->getProduct()->getInventoryItem();
        $purchaseOrder = $item->getOrder();
        foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
            if ($inventoryLevel->getWarehouse() !== $purchaseOrder->getWarehouse()) {
                continue;
            }

            $inventoryLevel->setPickLocation($pickupLocation);
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

        if (array_key_exists('is_partial', $options)) {
            $this->isPartial = $this->getOption($options, 'is_partial');
        }

        if (array_key_exists('pickup_location', $options)) {
            $this->pickupLocation = $this->getOption($options, 'pickup_location');
        }

        $this->options = $options;

        return $this;
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

    /**
     * @param $enumClass
     * @param $value
     * @return object|null
     */
    protected function getEnumValue($enumClass, $value)
    {
        $className = ExtendHelper::buildEnumValueClassName($enumClass);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->doctrineHelper
            ->getEntityRepositoryForClass($className);

        return $enumRepo->findOneBy(['id' => $value]);
    }

    /**
     * @param Allocation $allocation
     * @return void
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function updateAllocationWorkflow(Allocation $allocation)
    {
        /** @var WorkflowItem|null $workflowItem */
        $workflowItem = $this->doctrineHelper
            ->getEntityRepositoryForClass(WorkflowItem::class)
            ->findOneBy(
                [
                    'entityId' => $allocation->getId(),
                    'entityClass' => Allocation::class
                ]
            );

        if ($workflowItem) {
            $this->messageProducer->send(
                WorkflowTransitTopic::getName(),
                [
                    'workflow_item_entity_id' => $allocation->getId(),
                    'current_step_id' => $workflowItem->getCurrentStep()->getId(),
                    'entity_class' => Allocation::class,
                    'transition' => self::ALLOCATION_WORKFLOW_RESOLVED,
                    'jobId' => $this->generateJobId($allocation->getId()),
                    'priority' => MessagePriority::NORMAL
                ]
            );
        }
    }
}
