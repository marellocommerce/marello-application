<?php

namespace Marello\Bundle\InventoryBundle\Events;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class InventoryLogEvent extends Event
{
    const NAME = 'marello.inventory.log';

    /** @var InventoryItem */
    protected $inventoryItem;

    /** @var User */
    protected $user;

    /** @var string */
    protected $trigger;

    /** @var array */
    protected $payload = [];

    /** @var int */
    protected $oldQuantity;

    /** @var int */
    protected $newQuantity;
    /** @var int */

    protected $oldAllocatedQuantity;
    /** @var int */

    protected $newAllocatedQuantity;

    /**
     * @param InventoryItem $inventoryItem
     * @param string        $trigger
     *
     * @return InventoryLogEvent
     */
    public static function create(InventoryItem $inventoryItem, $trigger)
    {
        return new self($inventoryItem, $trigger);
    }

    /**
     * InventoryLogEvent constructor.
     *
     * @param InventoryItem $inventoryItem Inventory for which is this event created.
     * @param string        $trigger       Type of change.
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $trigger
    ) {
        $this->inventoryItem        = $inventoryItem;
        $this->trigger              = $trigger;
        $this->oldQuantity          = $this->newQuantity = $inventoryItem->getQuantity();
        $this->oldAllocatedQuantity = $this->newAllocatedQuantity = $inventoryItem->getAllocatedQuantity();
    }

    /**
     * @return InventoryItem
     */
    public function getInventoryItem()
    {
        return $this->inventoryItem;
    }

    /**
     * @param InventoryItem $inventoryItem
     *
     * @return $this
     */
    public function setInventoryItem($inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @param string $trigger
     *
     * @return $this
     */
    public function setTrigger($trigger)
    {
        $this->trigger = $trigger;

        return $this;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     *
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewQuantity()
    {
        return $this->newQuantity;
    }

    /**
     * @return int
     */
    public function getOldQuantity()
    {
        return $this->oldQuantity;
    }

    /**
     * @return int
     */
    public function getOldAllocatedQuantity()
    {
        return $this->oldAllocatedQuantity;
    }

    /**
     * @return int
     */
    public function getNewAllocatedQuantity()
    {
        return $this->newAllocatedQuantity;
    }

    /**
     * @param int $oldQuantity
     *
     * @return InventoryLogEvent
     */
    public function setOldQuantity($oldQuantity)
    {
        $this->oldQuantity = $oldQuantity;

        return $this;
    }

    /**
     * @param int $newQuantity
     *
     * @return InventoryLogEvent
     */
    public function setNewQuantity($newQuantity)
    {
        $this->newQuantity = $newQuantity;

        return $this;
    }

    /**
     * @param int $oldAllocatedQuantity
     *
     * @return InventoryLogEvent
     */
    public function setOldAllocatedQuantity($oldAllocatedQuantity)
    {
        $this->oldAllocatedQuantity = $oldAllocatedQuantity;

        return $this;
    }

    /**
     * @param int $newAllocatedQuantity
     *
     * @return InventoryLogEvent
     */
    public function setNewAllocatedQuantity($newAllocatedQuantity)
    {
        $this->newAllocatedQuantity = $newAllocatedQuantity;

        return $this;
    }
}
