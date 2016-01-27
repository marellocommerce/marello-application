<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Model\InventoryLogAction;

class InventoryLogActionHandler implements InventoryLogActionHandlerInterface
{
    /**
     * Handles InventoryLogAction.
     *
     * @param InventoryLogAction $action
     *
     * @return InventoryLog|null
     */
    public function handle(InventoryLogAction $action)
    {
        $log = new InventoryLog();

        return $log
            ->setActionType($action->getType())
            ->setChangeAmount($action->getQuantity())
            ->setInventoryItem($action->getInventoryItem())
            ->setUser($action->getUser());
    }
}
