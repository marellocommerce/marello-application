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

    /** @var int */
    protected $changeAmount;

    /** @var User */
    protected $user;

    /** @var string */
    protected $type;

    /** @var array */
    protected $payload = [];

    /**
     * InventoryLogEvent constructor.
     *
     * @param InventoryItem $inventoryItem Inventory for which is this event created.
     * @param int           $changeAmount  Amount how much has inventory changed.
     * @param string        $type          Type of change.
     * @param User          $user          User making the change.
     * @param array         $payload       Payload containing any additional parameters.
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $changeAmount,
        $type,
        User $user = null,
        array $payload = []
    ) {
        $this->inventoryItem = $inventoryItem;
        $this->changeAmount  = $changeAmount;
        $this->user          = $user;
        $this->type          = $type;
        $this->payload       = $payload;
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
     * @return int
     */
    public function getChangeAmount()
    {
        return $this->changeAmount;
    }

    /**
     * @param int $changeAmount
     *
     * @return $this
     */
    public function setChangeAmount($changeAmount)
    {
        $this->changeAmount = $changeAmount;

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
}
