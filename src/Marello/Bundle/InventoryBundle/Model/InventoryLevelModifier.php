<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelModifier
{
    const OPERATOR_INCREASE = 'increase';
    const OPERATOR_DECREASE = 'decrease';

    /** @var InventoryLevel $inventoryLevel */
    protected $inventoryLevel;

    /** @var int */
    protected $quantity = 0;

    /** @var string */
    protected $adjustmentOperator = self::OPERATOR_INCREASE;

    /** @var int */
    protected $allocatedStock = 0;

    /** @var string */
    protected $allocatedStockOperator = self::OPERATOR_INCREASE;

    /** @var EventDispatcherInterface $eventDispatcher */
    private $eventDispatcher;

    /**
     * InventoryItemModify constructor.
     *
     * @param $inventoryLevel
     */
    public function __construct($inventoryLevel)
    {
        $this->inventoryLevel = $inventoryLevel;
    }

    /**
     * @return mixed
     */
    public function getInventoryLevel()
    {
        return $this->inventoryLevel;
    }

    /**
     * @return InventoryLevel
     * @throws \Exception
     */
    public function toModifiedInventoryLevel()
    {
        if (!$this->eventDispatcher) {
            throw new \Exception();
        }

        list($stock, $allocatedStock) = $this->getContextData();

        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $this->inventoryLevel,
            $stock,
            $allocatedStock,
            'manual'
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );

        return $this->inventoryLevel;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
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
     * @return string
     */
    public function getAdjustmentOperator()
    {
        return $this->adjustmentOperator;
    }

    /**
     * @param string $operator
     *
     * @return $this
     */
    public function setAdjustmentOperator($operator)
    {
        $this->adjustmentOperator = $operator;

        return $this;
    }

    /**
     * Get Inventory Update context data
     * @return array
     */
    protected function getContextData()
    {
        $stock = $this->quantity * ($this->adjustmentOperator === self::OPERATOR_INCREASE ? 1 : -1);
        $allocatedStock = 1;//$this->allocatedStock * ($this->allocatedStockOperator === self::OPERATOR_INCREASE ? 1 : -1);
        return [$stock, $allocatedStock];
    }

    /**
     * Set the event dispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
