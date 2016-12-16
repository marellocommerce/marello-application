<?php

namespace Marello\Bundle\InventoryBundle\Manager\Balancer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Component\Action\Action\EventDispatcherAwareActionInterface;

use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

abstract class AbstractInventoryBalancer implements InventoryBalancerInterface, EventDispatcherAwareActionInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process(InventoryUpdateContext $context)
    {
        if ($this->canBalance($context)) {
            // dispatch oro_action.action.handle_before event
            // 1) dispatch before event
//            $this->eventDispatcher->dispatch(
//                ExecuteActionEvents::HANDLE_BEFORE,
//                new ExecuteActionEvent($context, $this)
//            );

            $this->balanceInventory($context);

            // dispatch oro_action.action.handle_after event
            // 3) dispatch after event
//            $this->eventDispatcher->dispatch(
//                ExecuteActionEvents::HANDLE_AFTER,
//                new ExecuteActionEvent($context, $this)
//            );
//
            // 4) dispatch inventoryUpdateEvent
//            $this->eventDispatcher->dispatch(
//                ExecuteActionEvents::HANDLE_AFTER,
//                new ExecuteActionEvent($context, $this)
//            );
        }
    }

    /**
     * Check if we can balance the inventory, by checking if there is a product
     * @param $context
     * @return mixed
     */
    protected function canBalance($context)
    {
        return $context->getValue('product');
    }

    /**
     * @param mixed $context
     */
    abstract protected function balanceInventory($context);
}
