<?php

namespace Marello\Bundle\InventoryBundle\Model\Allocation;

class WarehouseNotifierRegistry
{
    /**
     * @var WarehouseNotifierInterface[]
     */
    private $notifiers = [];

    /**
     * @param WarehouseNotifierInterface $notifier
     * @return $this
     */
    public function addNotifier(WarehouseNotifierInterface $notifier)
    {
        $this->notifiers[$notifier->getIdentifier()] = $notifier;
        
        return $this;
    }

    /**
     * @param string $identifier
     * @return null|WarehouseNotifierInterface
     */
    public function getNotifier($identifier)
    {
        if ($this->hasNotifier($identifier)) {
            return $this->notifiers[$identifier];
        }
        return null;
    }

    /**
     * @return WarehouseNotifierInterface[]
     */
    public function getNotifiers()
    {
        return $this->notifiers;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasNotifier($identifier)
    {
        if (array_key_exists($identifier, $this->notifiers)) {
            return true;
        }
        return false;
    }
}
