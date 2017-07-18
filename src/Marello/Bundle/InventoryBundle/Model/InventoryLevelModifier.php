<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelModifier
{
    const OPERATOR_INCREASE = 'increase';
    const OPERATOR_DECREASE = 'decrease';

    /** @var InventoryLevel $inventoryLevel */
    protected $inventoryLevel;

    /** @var int $quantity */
    protected $quantity = 0;

    /** @var string $adjustmentOperator */
    protected $adjustmentOperator = self::OPERATOR_INCREASE;

    /** @var int $allocatedInventory */
    protected $allocatedInventory = 0;

    /** @var Warehouse $warehouse */
    protected $warehouse;

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

        $inventoryQty = $this->quantity * ($this->adjustmentOperator === self::OPERATOR_INCREASE ? 1 : -1);
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $this->inventoryLevel->getInventoryItem()->getProduct(),
            $this->inventoryLevel->getInventoryItem(),
            $inventoryQty,
            $this->allocatedInventory,
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
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return $this
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

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
     * Set the event dispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
