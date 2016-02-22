<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\InventoryBundle\Model\InventoryType;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLogRepository")
 * @ORM\Table(name="marello_inventory_log")
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(type="string", length=32, nullable=false)
     *
     * @var string
     */
    protected $inventoryType = InventoryType::STANDARD;

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
     * @param string $actionType
     *
     * @return $this
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
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
}
