<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents changes in inventory items over time.
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLogRepository")
 * @ORM\Table(name="marello_inventory_log")
 * @ORM\HasLifecycleCallbacks()
 */
class InventoryLog
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
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $oldQuantity;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $newQuantity;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $oldAllocatedQuantity;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $newAllocatedQuantity;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $actionType;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var User
     */
    protected $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order", cascade={})
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Order
     */
    protected $order = null;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\InventoryItem")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var InventoryItem
     */
    protected $inventoryItem;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $createdAt = null;

    /**
     * InventoryLog constructor.
     *
     * @param InventoryItem $inventoryItem
     * @param string        $trigger
     */
    public function __construct(InventoryItem $inventoryItem, $trigger)
    {
        $this->inventoryItem = $inventoryItem;
        $this->actionType    = $trigger;

        $this->oldQuantity          = $this->newQuantity = $inventoryItem->getQuantity();
        $this->oldAllocatedQuantity = $this->newAllocatedQuantity = $inventoryItem->getAllocatedQuantity();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * @return InventoryItem
     */
    public function getInventoryItem()
    {
        return $this->inventoryItem;
    }

    /**
     * @return int
     */
    public function getOldQuantity()
    {
        return $this->oldQuantity;
    }

    /**
     * @param int $oldQuantity
     *
     * @return $this
     */
    public function setOldQuantity($oldQuantity)
    {
        $this->oldQuantity = $oldQuantity;

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
     * @param int $newQuantity
     *
     * @return $this
     */
    public function setNewQuantity($newQuantity)
    {
        $this->newQuantity = $newQuantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getOldAllocatedQuantity()
    {
        return $this->oldAllocatedQuantity;
    }

    /**
     * @param int $oldAllocatedQuantity
     *
     * @return $this
     */
    public function setOldAllocatedQuantity($oldAllocatedQuantity)
    {
        $this->oldAllocatedQuantity = $oldAllocatedQuantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewAllocatedQuantity()
    {
        return $this->newAllocatedQuantity;
    }

    /**
     * @param int $newAllocatedQuantity
     *
     * @return $this
     */
    public function setNewAllocatedQuantity($newAllocatedQuantity)
    {
        $this->newAllocatedQuantity = $newAllocatedQuantity;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}
