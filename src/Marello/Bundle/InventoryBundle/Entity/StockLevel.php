<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\UserBundle\Entity\User;

class StockLevel
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var InventoryItem
     */
    protected $inventoryItem;

    /**
     * @var int
     */
    protected $stock;

    /**
     * @var int
     */
    protected $allocatedStock;

    /**
     * @var string
     */
    protected $changeTrigger;

    /**
     * @var StockLevel
     */
    protected $previousLevel = null;

    /**
     * @var User
     */
    protected $author = null;

    /**
     * @var mixed
     */
    protected $subject = null;

    /**
     * @var string
     */
    protected $subjectType = null;

    /**
     * @var int
     */
    protected $subjectId = null;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * StockLevel constructor.
     *
     * @param InventoryItem $inventoryItem
     * @param int           $stock
     * @param int           $allocatedStock
     * @param string        $changeTrigger
     * @param StockLevel    $previousLevel
     * @param User          $author
     * @param mixed|null    $subject
     */
    public function __construct(
        InventoryItem $inventoryItem,
        $stock,
        $allocatedStock,
        $changeTrigger,
        StockLevel $previousLevel = null,
        User $author = null,
        $subject = null
    ) {
        $this->inventoryItem  = $inventoryItem->changeCurrentLevel($this);
        $this->stock          = $stock;
        $this->allocatedStock = $allocatedStock;
        $this->changeTrigger  = $changeTrigger;
        $this->previousLevel  = $previousLevel;
        $this->author         = $author;
        $this->subject        = $subject;
        $this->subjectType    = $subject !== null ? ClassUtils::getClass($subject) : null;
        $this->subjectId      = $subject !== null ? $subject->getId() : null;
        $this->createdAt      = new \DateTime();
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
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return int
     */
    public function getStockDiff()
    {
        return $this->stock - ($this->previousLevel ? $this->previousLevel->getStock() : 0);
    }

    /**
     * @return int
     */
    public function getAllocatedStock()
    {
        return $this->allocatedStock;
    }

    /**
     * @return int
     */
    public function getAllocatedStockDiff()
    {
        return $this->allocatedStock - ($this->previousLevel ? $this->previousLevel->getAllocatedStock() : 0);
    }

    /**
     * @return string
     */
    public function getChangeTrigger()
    {
        return $this->changeTrigger;
    }

    /**
     * @return StockLevel
     */
    public function getPreviousLevel()
    {
        return $this->previousLevel;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * @return int
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }
}
