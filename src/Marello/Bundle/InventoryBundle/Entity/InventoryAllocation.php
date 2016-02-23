<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_inventory_allocation")
 */
class InventoryAllocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var InventoryItem
     */
    protected $inventoryItem;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\OrderItem", cascade={})
     * @ORM\JoinColumn(nullable=true)
     *
     * @var OrderItem
     */
    protected $targetOrderItem = null;

    /**
     * InventoryAllocation constructor.
     *
     * @param InventoryItem $inventoryItem
     * @param int           $quantity
     */
    public function __construct(InventoryItem $inventoryItem, $quantity)
    {
        $this->inventoryItem = $inventoryItem;
        $this->quantity      = $quantity;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return OrderItem
     */
    public function getTargetOrderItem()
    {
        return $this->targetOrderItem;
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
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @param OrderItem $targetOrderItem
     *
     * @return $this
     */
    public function setTargetOrderItem($targetOrderItem = null)
    {
        $this->targetOrderItem = $targetOrderItem;

        return $this;
    }
}
