<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;

class InventoryItemUpdateApi
{
    /** @var InventoryItem|null */
    protected $inventoryItem = null;

    /** @var Warehouse */
    protected $warehouse;

    /** @var int */
    protected $stock;

    /**
     * InventoryItemUpdateApi constructor.
     *
     * @param InventoryItem|null $inventoryItem
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        InventoryItem $inventoryItem = null,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->inventoryItem    = $inventoryItem;
        $this->stock            = $inventoryItem ? $inventoryItem->getStock() : 0;
        $this->warehouse        = $inventoryItem ? $inventoryItem->getWarehouse() : null;
        $this->eventDispatcher  = $eventDispatcher;
    }

    /**
     * @return InventoryItem
     */
    public function toInventoryItem()
    {
        if (!$this->warehouse) {
            return null;
        }

        if (!$this->inventoryItem) {
            $this->inventoryItem = new InventoryItem($this->warehouse);
            $this->inventoryItem->setOrganization($this->warehouse->getOwner());
        }

        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $this->inventoryItem,
            $this->stock,
            null,
            'import'
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );

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
     * @param int $stock
     *
     * @return $this
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

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
     * Get Inventory Update context data
     * @return array
     */
    protected function getContextData()
    {
        $stock = $this->stock;
        $data = [
            'stock'             => $stock,
            'allocatedStock'    => null,
            'trigger'           => 'import',
            'items'             => [
                [
                    'item'          => $this->inventoryItem,
                    'qty'           => $stock,
                    'allocatedQty'  => null
                ]
            ]
        ];

        return $data;
    }
}
