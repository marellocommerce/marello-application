<?php

namespace Marello\Bundle\ReturnBundle\Workflow;

use Symfony\Contracts\Translation\TranslatorInterface;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InspectionAction extends AbstractAction
{
    /** @var array $options */
    protected $options = [];

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * InspectionAction constructor.
     * @param ContextAccessor $contextAccessor
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        parent::__construct($contextAccessor);

        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var ReturnEntity $return */
        $return = $context->getEntity();

        $return->getReturnItems()->map(function (ReturnItem $item) use ($return) {
            if (!$item->getReason() || !$item->getStatus()) {
                throw new \Exception(
                    $this->translator->trans('marello.return.returnentity.messages.error.return.workflow.consumer')
                );
            }
            if (($item->getReason()->getId() !== 'damaged') && ($item->getStatus()->getId() !== 'denied')) {
                $this->handleInventoryUpdate(
                    $item->getOrderItem(),
                    $item->getQuantity(),
                    null,
                    $return
                );
            }
        });
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param ReturnEntity $entity
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'return_workflow.inspection_ok',
            $entity
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     */
    public function initialize(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
