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
     * InventoryLogEvent constructor.
     *
     * @param InventoryItem $inventoryItem        Inventory for which is this event created.
     * @param int           $oldQuantity          Old inventory quantity.
     * @param int           $newQuantity          New Inventory quantity.
     * @param string        $trigger              Type of change.
     * @param int           $oldAllocatedQuantity Old allocated inventory quantity.
     * @param int           $newAllocatedQuantity New allocated Inventory quantity.
     * @param User          $user                 User making the change.
     * @param array         $payload              Payload containing any additional parameters.
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $oldQuantity,
        $newQuantity,
        $trigger,
        $oldAllocatedQuantity,
        $newAllocatedQuantity = null,
        User $user = null,
        array $payload = []
    ) {
        $this->inventoryItem        = $inventoryItem;
        $this->user                 = $user;
        $this->trigger              = $trigger;
        $this->payload              = $payload;
        $this->oldQuantity          = $oldQuantity;
        $this->newQuantity          = $newQuantity;
        $this->oldAllocatedQuantity = $oldAllocatedQuantity;
        $this->newAllocatedQuantity = $newAllocatedQuantity === null ? $oldAllocatedQuantity : $newAllocatedQuantity;
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
}
