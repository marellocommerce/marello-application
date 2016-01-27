<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\UserBundle\Entity\User;

class InventoryLogAction
{
    /** @var InventoryItem */
    private $inventoryItem;
    /** @var int */
    private $quantity;
    /** @var string */
    private $type;
    /** @var null|User */
    private $user;
    /** @var array */
    private $payload;

    /**
     * InventoryLogAction constructor.
     *
     * @param InventoryItem $inventoryItem InventoryItem related to this action.
     * @param int           $quantity      Quantity change caused by this action.
     * @param string        $type          Type of action.
     * @param User|null     $user          User responsible for generating this action (null if system).
     * @param array         $payload       Optional payload for action.
     */
    public function __construct(InventoryItem $inventoryItem, $quantity, $type, User $user = null, $payload = [])
    {
        $this->inventoryItem = $inventoryItem;
        $this->quantity      = $quantity;
        $this->type          = $type;
        $this->user          = $user;
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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null|User
     */
    public function getUser()
    {
        return $this->user;
    }
}
