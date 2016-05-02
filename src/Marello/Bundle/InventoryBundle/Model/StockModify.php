<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Oro\Bundle\UserBundle\Entity\User;

class StockModify
{
    /** @var string */
    protected $trigger;

    /** @var int */
    protected $stock;

    /** @var int */
    protected $allocatedStock;

    /** @var User */
    protected $author = null;

    /** @var mixed */
    protected $subject = null;

    /**
     * StockModify constructor.
     *
     * @param string $trigger
     * @param int    $stock
     * @param int    $allocatedStock
     */
    public function __construct($trigger, $stock, $allocatedStock = 0)
    {
        $this->trigger        = $trigger;
        $this->stock          = $stock;
        $this->allocatedStock = $allocatedStock;
    }

    /**
     * @param string $trigger
     * @param int    $stock
     *
     * @return StockModify
     */
    public static function modify($trigger, $stock)
    {
        return new self($trigger, $stock);
    }

    /**
     * @param string $trigger
     * @param int    $allocatedStock
     *
     * @return StockModify
     */
    public static function allocate($trigger, $allocatedStock)
    {
        return new self($trigger, 0, $allocatedStock);
    }

    /**
     * Creates a deallocation modification.
     *
     * @param StockLevel $stockLevel
     * @param string     $trigger
     * @param bool       $removeStock
     *
     * @return StockModify
     */
    public static function deallocation(StockLevel $stockLevel, $trigger, $removeStock = false)
    {
        return new self(
            $trigger,
            $removeStock ? -$stockLevel->getStockDiff() : 0,
            -$stockLevel->getAllocatedStockDiff()
        );
    }

    /**
     * @param User $author
     *
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
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
     * @param InventoryItem $item
     *
     * @return StockLevel
     */
    public function toCurrentStockLevel(InventoryItem $item)
    {
        $currentLevel   = $item->getCurrentLevel();
        $stock          = $currentLevel ? $currentLevel->getStock() : 0;
        $allocatedStock = $currentLevel ? $currentLevel->getAllocatedStock() : 0;

        $level = new StockLevel(
            $item,
            $stock + $this->stock,
            $allocatedStock + $this->allocatedStock,
            $this->trigger,
            $item->getCurrentLevel(),
            $this->author,
            $this->subject
        );

        $item->changeCurrentLevel($level);

        return $level;
    }
}
