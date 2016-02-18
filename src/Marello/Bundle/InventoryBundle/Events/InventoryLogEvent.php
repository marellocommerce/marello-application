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
    protected $type;

    /** @var array */
    protected $payload = [];

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
     * @param string        $type          Type of change.
     * @param User          $user          User making the change.
     * @param array         $payload       Payload containing any additional parameters.
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $oldQuantity,
        $newQuantity,
        $type,
        User $user = null,
        array $payload = []
    ) {
        $this->inventoryItem = $inventoryItem;
        $this->user          = $user;
        $this->type          = $type;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

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
}
