<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class OrderToPackingSlipMapper extends AbstractPackingSlipMapper
{
    /**
     * @var OrderWarehousesProviderInterface
     */
    protected $warehousesProvider;

    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        OrderWarehousesProviderInterface $warehousesProvider
    ) {
        parent::__construct($entityFieldProvider, $propertyAccessor);
        $this->warehousesProvider = $warehousesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function map($sourceEntity)
    {
        if (!($sourceEntity instanceof Order)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to OrderToPackingSlipMapper', get_class($sourceEntity))
            );
        }
        $packingSlips = [];
        foreach ($this->warehousesProvider->getWarehousesForOrder($sourceEntity) as $result) {
            /** @var Order $sourceEntity */
            $packingSlip = new PackingSlip();
            $data = $this->getData($sourceEntity, PackingSlip::class);
            $data['order'] = $sourceEntity;
            $warehouse = $result->getWarehouse();
            $data['warehouse'] = $warehouse;
            $data['items'] = $this->getItems($result->getOrderItems(), $warehouse);

            $this->assignData($packingSlip, $data);
            $packingSlips[] = $packingSlip;
        }
        return $packingSlips;
    }

    /**
     * @param Collection $items
     * @param Warehouse $warehouse
     * @return ArrayCollection
     */
    protected function getItems(Collection $items, Warehouse $warehouse)
    {
        $orderItems = $items->toArray();
        $packingSlipItems = [];
        /** @var OrderItem $item */
        foreach ($orderItems as $item) {
            $packingSlipItems[] = $this->mapItem($item, $warehouse);
        }

        return new ArrayCollection($packingSlipItems);
    }

    /**
     * @param OrderItem $orderItem
     * @param Warehouse $warehouse
     * @return PackingSlipItem
     */
    protected function mapItem(OrderItem $orderItem, Warehouse $warehouse)
    {
        $packingSlipItem = new PackingSlipItem();
        $packingSlipItemData = $this->getData($orderItem, PackingSlipItem::class);
        /** @var Product $product */
        $product = $orderItem->getProduct();
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $product->getInventoryItems()->first();
        if ($inventoryItem) {
            if ($inventoryLevel = $inventoryItem->getInventoryLevel($warehouse)) {
                $inventoryBatches = $inventoryLevel->getInventoryBatches()->toArray();
                if (count($inventoryBatches) > 0) {
                    usort($inventoryBatches, function (InventoryBatch $a, InventoryBatch $b) {
                        if ($a->getDeliveryDate() < $b->getDeliveryDate()) {
                            return -1;
                        } elseif ($a->getDeliveryDate() > $b->getDeliveryDate()) {
                            return 1;
                        } else {
                            return 0;
                        }
                    });
                    $data = [];
                    $quantity = $orderItem->getQuantity();
                    /** @var InventoryBatch[] $inventoryBatches */
                    foreach ($inventoryBatches as $inventoryBatch) {
                        if ($inventoryBatch->getQuantity() >= $quantity) {
                            $data[$inventoryBatch->getBatchNumber()] = $quantity;
                            break;
                        } elseif (($batchQty = $inventoryBatch->getQuantity()) > 0) {
                            $data[$inventoryBatch->getBatchNumber()] = $batchQty;
                            $quantity = $quantity - $batchQty;
                        }
                    }
                    $packingSlipItemData['inventoryBatches'] = $data;
                }
            }
        }
        $packingSlipItemData['weight'] = ($product->getWeight() * $orderItem->getQuantity());
        $packingSlipItemData['orderItem'] = $orderItem;
        $this->assignData($packingSlipItem, $packingSlipItemData);

        return $packingSlipItem;
    }
}
