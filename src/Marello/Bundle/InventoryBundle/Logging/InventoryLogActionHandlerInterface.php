<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Model\InventoryLogAction;

interface InventoryLogActionHandlerInterface
{
    /**
     * Handles InventoryLogAction.
     *
     * @param InventoryLogAction $action
     *
     * @return InventoryLog|null
     */
    public function handle(InventoryLogAction $action);
}
