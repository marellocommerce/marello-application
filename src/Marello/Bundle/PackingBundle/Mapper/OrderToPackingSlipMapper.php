<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\ProductBundle\Entity\Product;

class OrderToPackingSlipMapper extends AbstractPackingSlipMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($sourceEntity)
    {
        if (!($sourceEntity instanceof Allocation)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to OrderToPackingSlipMapper', get_class($sourceEntity))
            );
        }
        /** @var Allocation $sourceEntity */
        $packingSlip = new PackingSlip();
        $data = $this->getData($sourceEntity->getOrder(), PackingSlip::class);
        $data['order'] = $sourceEntity->getOrder();
        $data['warehouse'] = $sourceEntity->getWarehouse();
        $data['sourceEntity'] = $sourceEntity;
        $data['items'] = $this->getItems($sourceEntity);

        $this->assignData($packingSlip, $data);

        return [$packingSlip];
    }

    /**
     * @param Collection $items
     * @return ArrayCollection
     */
    protected function getItems(Allocation $sourceEntity)
    {
        $allocationItems = $sourceEntity->getItems()->toArray();
        $packingSlipItems = [];
        /** @var AllocationItem $item */
        foreach ($allocationItems as $item) {
            $packingSlipItems[] = $this->mapItem($item, $sourceEntity->getWarehouse());
        }

        return new ArrayCollection($packingSlipItems);
    }

    /**
     * @param AllocationItem $allocationItem
     * @return PackingSlipItem
     */
    protected function mapItem($allocationItem, Warehouse $warehouse)
    {
        $packingSlipItem = new PackingSlipItem();
        $packingSlipItemData = $this->getData($allocationItem, PackingSlipItem::class);
        $product = $allocationItem->getProduct();
        $inventoryItem = $product->getInventoryItem();
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
                    $quantity = $allocationItem->getQuantity();
                    /** @var InventoryBatch[] $inventoryBatches */
                    $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
                    foreach ($inventoryBatches as $inventoryBatch) {
                        // we cannot use expired batches
                        if ($inventoryBatch->getSellByDate() && $inventoryBatch->getSellByDate() <= $currentDateTime) {
                            continue;
                        }
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
        $packingSlipItemData['weight'] = ($product->getWeight() * $allocationItem->getQuantity());
        $packingSlipItemData['orderItem'] = $allocationItem->getOrderItem();
        $packingSlipItemData['productUnit'] = $allocationItem->getOrderItem()->getProductUnit();
        $this->assignData($packingSlipItem, $packingSlipItemData);

        return $packingSlipItem;
    }
}
