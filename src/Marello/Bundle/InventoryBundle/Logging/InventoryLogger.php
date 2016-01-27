<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Exception\InventoryLogException;
use Marello\Bundle\InventoryBundle\Model\InventoryLogAction;

class InventoryLogger
{
    /** @var InventoryLogActionHandlerInterface[] */
    protected $handlers = [];

    /** @var Registry */
    protected $doctrine;

    /**
     * InventoryLogger constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Adds action handler of given type to logger.
     *
     * @param string                             $type
     * @param InventoryLogActionHandlerInterface $handler
     *
     * @return $this
     */
    public function addActionHandler($type, InventoryLogActionHandlerInterface $handler)
    {
        if (array_key_exists($type, $this->handlers)) {
            throw new InventoryLogException(
                sprintf('Handler for action type "%s" is already registered.', $type)
            );
        }

        $this->handlers[$type] = $handler;

        return $this;
    }

    /**
     * Creates a log entry for inventory action.
     *
     * @param InventoryLogAction $action
     */
    public function log(InventoryLogAction $action)
    {
        if (!array_key_exists($action->getType(), $this->handlers)) {
            throw new InventoryLogException(
                sprintf('Unknown inventory log action type "%s". Please check if corresponding handler is registered.')
            );
        }

        /*
         * Use handler to handle given action.
         */
        $inventoryLog = $this->handlers[$action->getType()]->handle($action);

        /*
         * Store generated log entry.
         */
        $this->doctrine->getManager()->persist($inventoryLog);
        $this->doctrine->getManager()->flush();
    }
}
