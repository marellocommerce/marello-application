<?php

namespace Marello\Bundle\InventoryBundle\Events;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryType;
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

    /** @var string */
    protected $inventoryType;

    /** @var int */
    private $oldQuantity;

    /** @var int */
    private $newQuantity;

    /**
     * InventoryLogEvent constructor.
     *
     * @param InventoryItem $inventoryItem Inventory for which is this event created.
     * @param int           $oldQuantity   Old inventory quantity.
     * @param int           $newQuantity   New Inventory quantity.
     * @param string        $trigger       Type of change.
     * @param string        $inventoryType Type of inventory change.
     * @param User          $user          User making the change.
     * @param array         $payload       Payload containing any additional parameters.
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $oldQuantity,
        $newQuantity,
        $trigger,
        $inventoryType = InventoryType::STANDARD,
        User $user = null,
        array $payload = []
    ) {
        $this->inventoryItem = $inventoryItem;
        $this->user          = $user;
        $this->trigger       = $trigger;
        $this->inventoryType = $inventoryType;
        $this->payload       = $payload;
        $this->oldQuantity   = $oldQuantity;
        $this->newQuantity   = $newQuantity;
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
     * @return string
     */
    public function getInventoryType()
    {
        return $this->inventoryType;
    }
}
